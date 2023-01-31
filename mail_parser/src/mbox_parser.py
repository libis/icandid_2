#! /usr/bin/env python3
# ~*~ utf-8 ~*~

# https://gist.github.com/benwattsjones/060ad83efd2b3afc8b229d41f9b246c4

import mailbox
import hashlib
import json
import re
import base64
from datetime import datetime
import dateutil.parser as dateutil_parser
from random import randrange
import requests
import yaml
import os
import redis
import pdfkit 
import email
from email.parser import BytesParser
from email.policy import default
import logging, sys,getopt




def read_config(file_path):
    with open(file_path, "r") as f:
        filename, file_extension = os.path.splitext(file_path)
        if file_extension in ['.yaml','.yml']:
            return yaml.safe_load(f)
        if file_extension in ['.json','.cfg']:
            return json.load(f)

config_file = "/app/config/config.yml"
config = read_config(  os.path.join( os.path.abspath(os.path.dirname(__file__)), config_file) )

ingest_file = "/app/config/ingest.cfg"
ingest = read_config(  os.path.join( os.path.abspath(os.path.dirname(__file__)), ingest_file) )


try: 
    print ( config['DEBUG'] )
    if config['DEBUG']:
        print ( config['DEBUG'] )
        logging.basicConfig(stream=sys.stderr, level=logging.DEBUG)
    else:
        logging.basicConfig(stream=sys.stderr, level=logging.INFO)
except :
    logging.basicConfig(stream=sys.stderr, level=logging.INFO)



    
class SchemaOrgMessage():
  def __init__(self):
    self.recipient = 'recipient'
    self.sender = 'sender'    
    self.text = 'text'

class GmailMboxMessage():

    def __init__(self, email_data):
        
        self.redis = redis.Redis(
            host=config['REDIS']['HOST'],
            port=config['REDIS']['PORT'],
            db=config['REDIS']['DB'])    

        if not isinstance(email_data, email.message.EmailMessage):
            raise TypeError('Variable must be email.message.EmailMessage')

        self.email_data = email_data
        self.decode_header()

    def parse_email(self):
        
        self.email_id =  self.email_data['Message-ID'].strip().lstrip('<').rstrip('>')
        self.email_labels = self.email_data['X-Gmail-Labels']

        self.email_date = dateutil_parser.parse( re.sub('(\([^\)]+\))', '', self.email_data['Date'] )    ).strftime('%Y-%m-%d %H:%M:%S')

        self.email_subject = self.email_data['Subject']
        self.email_from = self.decode_addresses( email.utils.getaddresses ( [ self.email_data['From'] ] ) )
        self.email_to = []
        if self.email_data['To'] and self.email_data['To'] != "undisclosed-recipients:;":
            self.email_to +=  self.decode_addresses( email.utils.getaddresses ( [ self.email_data['To'] ] ) )
        if self.email_data['Cc']:
            self.email_to += self.decode_addresses( email.utils.getaddresses ( [ self.email_data['Cc'] ] ) )
        if self.email_data['Bcc']:
            self.email_to += self.decode_addresses( email.utils.getaddresses ( [ self.email_data['Bcc'] ] ) )

        self.email_delivered = self.email_data[' Delivered-To']
        self.email_cc = self.email_data['Cc']
        self.email_bcc = self.email_data['Bcc']

        self.email_payload = self.read_email_payload() 
        
        self.email_received = self.email_data['Received']
        self.maintype = self.email_data.get_content_maintype()

    def decode_addresses (self, addresses ):
        for idx, address in enumerate(addresses):
            address = list(address)
            for i, a in enumerate(address):
                text, encoding = email.header.decode_header( a )[0]
                if text and encoding:
                    address[i] = text.decode(encoding) 
            addresses[idx] = tuple(address)   
        return addresses

    def decode_header(self):
        for key in self.email_data.keys():
            if key not in ['From','To','Cc','Bcc']:
                text, encoding = email.header.decode_header( self.email_data[key] )[0]
                if text and encoding:
                    self.email_data.replace_header ( key , text.decode(encoding) )

    def read_email_payload(self):
        email_payload = self.email_data.get_payload()
        if self.email_data.is_multipart():
            email_messages = list(self._get_email_messages(email_payload))
        else:
            email_messages = [self.email_data]
        return [self._read_email_text(msg) for msg in email_messages]

    def _get_email_messages(self, email_payload):
        for msg in email_payload:
            if isinstance(msg, (list,tuple)):
                for submsg in self._get_email_messages(msg):
                    yield submsg
            elif msg.is_multipart():
                for submsg in self._get_email_messages(msg.get_payload()):
                    yield submsg
            else:
                yield msg

    def _read_email_text(self, msg):

        content_type = 'NA' if isinstance(msg, str) else msg.get_content_type()
        charset      = 'utf-8' if isinstance(msg, str) else msg.get_content_charset()
        encoding     = 'NA' if isinstance(msg, str) else msg.get('Content-Transfer-Encoding', 'NA')
        langauge     = 'NA' if isinstance(msg, str) else msg.get('Content-Language', 'NA')
        description  = 'NA' if isinstance(msg, str) else msg.get('Content-Description', 'NA')
        content_disposition = 'NA' if isinstance(msg, str) else msg.get_content_disposition()

        if description == 'NA':
            content_disposition_list = self.header_elm_to_dict( msg.get('Content-Disposition', 'NA').split('; ') )
            if 'filename' in content_disposition_list:
                description = content_disposition_list['filename']

        msg_text     =  msg.get_content()
        
        content_id = msg.get('Content-ID').strip().lstrip('<').rstrip('>') if msg.get('Content-ID') else 'NA'

        retval={
            'content_type': content_type,
            'encoding': encoding,
            'content_body': msg_text,
            'content_id': content_id,
            'langauge': langauge,
            'charset': charset,
            'content_disposition': content_disposition,
            'description': description
        }

        return retval

    def header_elm_to_dict(self, l):
        h = {}
        for elm in l:
            a_elm = elm.split("=")
            if len(a_elm) > 1:
                h[a_elm[0]] =  a_elm[1].strip('\"')
        return h   

    def sha_256(self, in_str):
        return hashlib.sha256( " ".join( in_str.split() ).encode('utf-8') ).hexdigest()

    def email_data_to_schemaorg_person(self, emailaddress):

        if isinstance( emailaddress , str):
            regexp = re.compile('.+<.*>')
            if regexp.search(emailaddress):
                regexp = re.compile('(.*)<(.*)>')
                match = regexp.match(' '.join(emailaddress.split()))
                name = match.group(1).strip()
                emailaddress = match.group(2).strip()
            else:
                regexp = re.compile('^<?([^@]+)@([^@>]+)>?$')
                match = regexp.match(emailaddress)
                name = match.group(1).strip()
                emailaddress = match.group(1).strip() +'@'+ match.group(2).strip()
        else:                
            name =  emailaddress[0] if emailaddress[0] != "" else emailaddress[1]
            emailaddress = emailaddress[1]

        # logging.debug( "name: "+ name  )
        # logging.debug( "emailaddress: "+ emailaddress  )

        sha256_mail =  hashlib.sha256( " ".join( emailaddress.split() ).encode('utf-8') ).hexdigest()
        person = self.redis.get(sha256_mail)

        if person:
            person = json.loads(person)
            name_id = person["id"]
        else:
            total_keys = self.redis.dbsize()
            name_id = "PERSON_"+'{:010d}'.format(total_keys+1)
            self.redis.mset({sha256_mail: json.dumps( { "id" : name_id , "mail": emailaddress } ) })

        name =  hashlib.sha256( " ".join( name.split() ).encode('utf-8') ).hexdigest()

        person = {
            "@type": "Person",
            "@id": sha256_mail,
            "name": name_id,
            "alternateName": name,
            "email": sha256_mail
        }
        return person

######################### 


def process_mbox(file_to_process, records_dir, ingest):

    mbox_obj = mailbox.mbox(file_to_process, factory=BytesParser(policy=default).parse)
    num_entries = len(mbox_obj)


    logging.info(  "num_entries: " + str(num_entries) )

    #for idx, email_obj in enumerate(list(mbox_obj)[92:100]):
    for idx, email_obj in enumerate(list(mbox_obj)):
        email_data = GmailMboxMessage(email_obj)

        email_data.parse_email()

        # print ('----------------------------------------------------------------------')
        logging.debug( f"Start processing {email_data.email_id,}") 
        schema_org={
            "@context": ingest["@context"],
            "additionalType": "CreativeWork",
            "inLanguage": ingest["inLanguage"],
            "@type": ingest["@type"],
            "additionalType": "CreativeWork",
            "isBasedOn" : {
                "provider" : ingest["provider"],
                "@type" : "CreativeWork",
                "name" : ingest["genericRecordDesc"] ,
                "@id" :ingest["@id"],
                "isPartOf" : ingest["dataset"],
            },

            "@id"  : email_data.email_id,
            "name": ingest["genericMailSubject"],
            "text": ""
            
        }

        logging.debug( "get sender, creator, author, ...") 

        schema_org["sender"]  = list( map( email_data.email_data_to_schemaorg_person ,  email_data.email_from ) )
        schema_org["creator"] = schema_org["sender"] 
        schema_org["author"]  = schema_org["sender"] 
        schema_org["recipient"] = list( map( email_data.email_data_to_schemaorg_person,  email_data.email_to) )

        logging.debug( "get datePublished and name") 

        schema_org["datePublished"] = email_data.email_date
        
        if email_data.email_subject != "":
            schema_org["name"] = email_data.email_subject
        
        logging.debug( "get email_plain_body") 
        
        schema_org["associatedMedia"] = []
        body_assets=[]
        email_plain_body = [email_part for email_part in email_data.email_payload if email_part['content_type'] in ['text/plain']]
        email_rtf_body  = [email_part for email_part in email_data.email_payload if ( email_part['content_type'] in ['application/rtf'] and email_part["content_disposition"] != "attachment") ]
        email_html_body  = [email_part for email_part in email_data.email_payload if ( email_part['content_type'] in ['text/html'] and email_part["content_disposition"] != "attachment") ]
        
        if ( len(email_plain_body) > 0):
            body_assets.append(email_plain_body[0]['content_id'])
            #email_plain_body = email_plain_body[0]['content_body'].decode( email_plain_body[0]['charset'] )
            plain_body = email_plain_body[0]['content_body']

        if ( len(email_plain_body) == 0):
            logging.warning( 'No email part with content_type \'text/plain\' available!')
        elif ( len(email_plain_body) > 1 ):
            logging.warning( 'More than 1 email part with content_type \'text/plain\' !')
         
        logging.debug( "get email_html_body")  
        if ( len(email_html_body) > 0):     
            body_assets.append(email_html_body[0]['content_id'])
            html_body = email_html_body[0]['content_body']         
        if ( len(email_html_body) == 0):
            logging.warning( 'No email part with content_type \'text/html\' available!')
        elif (len(email_html_body) > 1 ):
            logging.warning( 'More than 1 email part with content_type \'text/html\' !')


        if (len(email_plain_body) == 0 and len(email_html_body) > 0):
            plain_body = html_body
        if (len(email_html_body) == 0 and len(email_plain_body) > 0):
            html_body = plain_body
        
        logging.debug( "replace images in html_body") 
        inline_assets=[]
        for pic in re.finditer(r'cid:([^"]+)', html_body):
            img_src = next((email_part for email_part in email_data.email_payload if email_part["content_id"] == pic[1]), None)
            if img_src:
                inline_assets.append(pic[1])
                html_body = re.sub(r'src="cid:'+ pic[1] +'"', f'src="data:{ img_src["content_type"] };base64, { base64.b64encode(img_src["content_body"]).decode("utf8") }"', html_body)
            else:
                html_body = re.sub(r'src="cid:'+ pic[1] +'"', '', html_body)

        schema_org["text"] = plain_body

        logging.debug (f"try to save pdf to :{email_data.email_id}")
        try:
            pdfkit.from_string(html_body,records_dir + email_data.email_id +".pdf", options={'encoding': 'UTF-8'} )    
        except OSError as err:
            logging.error(f"Error saving pdf :{ err }")
       

        logging.debug (f"add pdf to json :{email_data.email_id}")
        with open(records_dir + email_data.email_id +".pdf", "rb") as pdf_file:
            schema_org["associatedMedia"].append( {
                "@id": email_data.email_id,
                "@type": "MediaObject", 
                "encodingFormat": "application/pdf",
                "name": "Message_"+ email_data.email_id +"_as_pdf.pdf",
                "contentUrl": "data:application/pdf;base64,"+ base64.b64encode(pdf_file.read()).decode("utf8") 
                } )

        attached_assets =  [email_part for email_part in email_data.email_payload if ( email_part['content_id'] not in inline_assets and email_part['content_id'] not in body_assets)  ]

        logging.debug (f"processing { len(attached_assets)} attachments ")
        
        for attachment in attached_assets: 
            logging.debug ( "processing attachment: "+ str(attachment["content_id"]) )
        
            if attachment["encoding"] == "base64":
                if isinstance( attachment["content_body"] , str):
                    attachment_body =  base64.b64encode( attachment["content_body"].encode("utf8") ).decode("utf8") 
                else:
                    attachment_body = base64.b64encode( attachment["content_body"] ).decode("utf8") 
            
                schema_org["associatedMedia"].append({ 
                    "@id": email_data.email_id +"_"+ attachment["content_id"],  
                    "@type": "MediaObject", 
                    "name": attachment["description"],
                    "encodingFormat": attachment["content_type"],
                    "contentUrl": "data:"+ attachment["content_type"] +";base64,"+ attachment_body
                    } )

        if not schema_org["associatedMedia"] :
            del schema_org["associatedMedia"]
        
        # if schema_org.get('raw_htlm_text', None):
        #    schema_org["text"] = schema_org["raw_htlm_text"] 
        #     schema_org["raw_htlm_text"] = get_anonymized_text( schema_org["raw_htlm_text"]  )


        with open(records_dir + email_data.email_id +".json", "w", encoding='utf8') as outfile:
            outfile.write(json.dumps(schema_org, indent=2, ensure_ascii=False))
            
    #    print( json.dumps( schema_org ) )
        logging.info('Parsing email {0} of {1}'.format(idx+1, num_entries))

def main(argv):

    file_to_process = config['SOURCE_MAILBOX']
    records_dir = config['RECORDS_DIR']
      
    opts, args = getopt.getopt(argv,"hs:d:",["help","source=","destination="])
    for opt, arg in opts:
        if opt in ("-h", "--help"):
            print ('/app/src/gmail_mbox_parser.py -s <file_to_process> -d <destination>')
            sys.exit()
        elif opt in ("-s", "--source"):
            file_to_process = arg
        elif opt in ("-d", "--destination"):            
            records_dir = arg
        else:
            assert False, "unhandled command line option"


    if not os.path.isdir(records_dir):
       os.makedirs(records_dir)     
    
    logging.debug (f"file_to_process: { file_to_process }  ")
    logging.debug (f"records_dir: { records_dir }  ")
    logging.debug (f"ingest: { ingest }  ")
  
    process_mbox(file_to_process, records_dir, ingest)

if __name__ == "__main__":
    main(sys.argv[1:])

