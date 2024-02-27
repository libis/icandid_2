import spacy
from spacy.cli.download import get_compatibility 
import json
import requests
import datetime
import random
import os
import fcntl
import copy
import sys
from datetime import datetime

requests.packages.urllib3.disable_warnings(requests.packages.urllib3.exceptions.InsecureRequestWarning)

esquery={
  "_source": [
    "name",
    "@id",
    "@type",
    "headline",
    "description",
    "articleBody",
    "text",
    "prov:wasAttributedTo",
    "inLanguage",
    "@context"    
  ],
  "sort": {
    "sdDatePublished": {
      "order": "desc"
    }
  },
  "size": 1,    
  "query": {
    "bool": {
      "must": [
        {
          "bool":{
            "should":[]
          }
        },
        {
          "bool": {
            "should": []
          }
        }
      ],
      "must_not": [
        {
          "nested": {
            "path": "prov:wasAttributedTo.prov:wasAssociatedFor",
            "query": {
              "bool": {
                "should": []
              }
            }
          }
        }
      ]
    }
  }
}
esquery2={
    "_source": [
        "name",
        "@id",
        "@type",
        "headline",
        "description",
        "articleBody",
        "text",
        "prov:wasAttributedTo",
        "inLanguage",
        "@context"
    ],
    "sort": {
        "sdDatePublished": {"order": "desc"}
    },
    "size": 1,
    "query": {
        "bool": {
            "must": [
                {
                    "terms": {
                        "prov:wasAttributedTo.name.keyword": ["SpaCy"]
                    }
                },
                {
                    "bool": {
                        "should": []
                    }
                },
                {
                    "bool": {
                        "should": []
                    }
                }
            ],
            "must_not": {
                "terms": {
                    "prov:wasAttributedTo.version.keyword": ["3.7.1"]
                }
            }
        }
    }
}

newblock = {
  "prov:wasAssociatedFor": [],
  "@type": [
      "prov:Agent",
      "agent"
  ],
  "prov:type": "prov:SoftwareAgent",
  "name": "SpaCy",
  "description": "Free open-source library for Natural Language Processing in Python",
  "@id": "spacy",
  "version": "",
  "url": "https://spacy.io/"
}
newblock["version"] = spacy.__version__

newblock2 = {
  "prov:used" : [
    {
      "itemListElement" : [
        "headline",
        "description",
        "articleBody",
        "text"
      ],
      "@type" : "itemListElement",
      "name" : "Used fields from the attributed entity",
      "description" : "list of fields from the record that are used in this enrichment process",
      "@id" : "used_fields_for_enrichment"
    },
    {
      "name" : "English pipeline",
      "@id" : "en_core_web_lg",
      "version" : "3.2.0",
      "url" : "https://spacy.io/models/en"
    }
  ],
  "prov:generatedAtTime": "2023-08-20T05:16:20.112756",
  "@type": [
      "prov:Activity",
      "action"
  ],
  "name": "French Named Entity Recognition",
  "@id": "spacy_ner_fr_core_web_lg",
  "_generated": {},
  "prov:generated": []
}

config_file = './' + sys.argv[1]
print(datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "Opening configuration " + config_file);
config = json.load(open(config_file))


models = config["models"]
elastic_url = config["elastic_url"] + config["elastic_index"] + '/_search?track_total_hits=true'
update_url = config["elastic_url"] + config["elastic_index"] + '/_update/'
user = config["elastic_user"]
passwd = config["elastic_password"]
esquery["size"] = config["querysize"]
esquery2["size"] = config["querysize"]
esquery2["query"]["bool"]["must_not"]["terms"]["prov:wasAttributedTo.version.keyword"] = [spacy.__version__] 

headers = {"Content-Type":"application/json", "Accept":"application/json"}

process_fields = ['headline','description','articleBody','text']

fh=0
def run_once():
    global fh
    fh=open(os.path.realpath(__file__),'r')
    try:
        fcntl.flock(fh,fcntl.LOCK_EX|fcntl.LOCK_NB)
    except:
        print(datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "Still busy so not going to start again");
        os._exit(0)

def findinout(text, label, l) :
    for i in range(len(l)):
        if (l[i]["value"] == text and l[i]["label"] == label):
            return i
    return 0


if __name__ == '__main__':

    run_once()

    nlp = {}

    starttime = datetime.now()

    

    for model in models:
      mod = {"term":{"prov:wasAttributedTo.prov:wasAssociatedFor.@id":"spacy_ner_"+models[model]["@id"]}}
      esquery["query"]["bool"]["must_not"][0]["nested"]["query"]["bool"]["should"].append(mod)
      for f in ["name","headline","description","articleBody","text"]:
        esquery["query"]["bool"]["must"][0]["bool"]["should"].append({"term":{f+".@language.keyword":model}})
        esquery2["query"]["bool"]["must"][1]["bool"]["should"].append({"term":{f+".@language.keyword":model}})
    for f in process_fields:
      esquery["query"]["bool"]["must"][1]["bool"]["should"].append({"exists":{"field":f}})
      esquery2["query"]["bool"]["must"][2]["bool"]["should"].append({"exists":{"field":f}})

#    print(json.dumps(esquery, indent=4))

    res = requests.post(elastic_url, auth=(user, passwd), data=json.dumps(esquery), verify=False, headers=headers)


    data = json.loads(res.text)
#    print(json.dumps(data, indent=4, sort_keys=True))
    if (data["hits"]["total"]["value"] == 0):
      print(json.dumps(esquery2, indent=4))
      exit()
      res = requests.post(elastic_url, auth=(user, passwd), data=json.dumps(esquery2), verify=False, headers=headers)  
      data = json.loads(res.text)
      if (data["hits"]["total"]["value"] == 0):
        exit()

    for article in data["hits"]["hits"]:
        doc_id = article["_id"]
        print(datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "NER : " + doc_id)        

        texts = {}

        try:
          euquery = copy.deepcopy(article["prov:wasAttributedTo"])
        except KeyError:
          euquery = []
        
        newblock["prov:wasAssociatedFor"] = []


        # if already exists remove it because we will update
        for i, x in reversed(list(enumerate(euquery))):
            if x["@id"] == "spacy":
                del(euquery[i])

        for f in process_fields:

            if f in article["_source"]:
              if (isinstance(article["_source"][f], list)):
                if article["_source"][f][0]["@language"] not in texts.keys():
                    texts[article["_source"][f][0]["@language"]] = {}
                texts[article["_source"][f][0]["@language"]][f] = article["_source"][f][0]["@value"].replace("\r\n", " ")                 
              else:
                if article["_source"][f]["@language"] not in texts.keys():
                    texts[article["_source"][f]["@language"]] = {}
                texts[article["_source"][f]["@language"]][f] = article["_source"][f]["@value"].replace("\r\n", " ") 


#        print(json.dumps(texts, indent=4, sort_keys=True))


        for lang,fields in texts.items():
            content = ""
#            print(json.dumps(fields, indent=4, sort_keys=True))
            if lang in models.keys():
                if lang not in nlp.keys():
                    nlp[lang] = spacy.load(models[lang]["@id"])

                for f in process_fields:
                    if (f not in fields.keys()):
                        fields[f] = "";
#                print(json.dumps(fields, indent=4, sort_keys=True))
                if ((fields["headline"] not in fields["description"]) and (fields["headline"] not in fields["articleBody"]) and (fields["headline"] not in fields["text"])):
                    content += fields["headline"] + " "
                if ((fields["description"] not in fields["articleBody"]) and (fields["description"] not in fields["text"])):
                    content += fields["description"] + " "
                if (fields["text"] not in fields["articleBody"]):
                    content += fields["text"] + " "

                content += fields["articleBody"]

                content = content.replace("\r\n", " ")
            
                doc = nlp[lang](content[0:1000000])


                out = []

                for ent in doc.ents:
                    if (ent.label_ != "DATE" and ent.label_ != "ORDINAL" and ent.label_ != "CARDINAL" and ent.label_ != "PERCENT" and ent.label_ != "TIME" and ent.label_ != "MONEY" and ent.label_ != "QUANTITY"):
                        idx = findinout(ent.text, ent.label_, out);
                        if (idx == 0):
                            new = {"value":ent.text, "label":ent.label_, "count":1}
                            out.append(new)
                        else: 
                            out[idx]["count"] += 1
#                print(json.dumps(out, indent=4, sort_keys=True))
                newblock2["prov:used"][1]["name"] = models[lang]["name"] + " pipeline"
                newblock2["prov:used"][1]["@id"] = models[lang]["@id"]
                newblock2["prov:used"][1]["version"] = get_compatibility()[models[lang]["@id"]][0]
                newblock2["prov:used"][1]["url"] = models[lang]["url"]
                newblock2["prov:generatedAtTime"] = datetime.now().strftime("%Y-%m-%dT%H:%M:%S.%f")
                newblock2["name"] = models[lang]["longname"]
                newblock2["@id"] = "spacy_ner_" + models[lang]["@id"]
                newblock2["_generated"] = {}
                newblock2["_generated"]["ALL"] = list(map(lambda x: x["value"], out))
                for l in list(map(lambda x: x["label"], out)):
                  newblock2["_generated"][l] = list(map(lambda y : y["value"], list(filter(lambda x : x["label"] == l, out))))

                newblock2["prov:generated"] = []
                newblock2["prov:generated"] = list(map(lambda x: {"additionalType":x["label"],"name":{"@value":x["value"],"@language":lang},"value":x["count"]}, out))
                newblock["prov:wasAssociatedFor"].append(newblock2)

        euquery.append(newblock) 

#        print(update_url + doc_id)
#        print(json.dumps(out, indent=4, sort_keys=True))

#        print(json.dumps({"doc":{"prov:wasAttributedTo":euquery}}, indent=4, sort_keys=True))
        if (len(euquery) != 0):
          res2 = requests.post(update_url + doc_id, auth=(user, passwd), data=json.dumps({"doc":{"prov:wasAttributedTo":euquery}}), verify=False, headers=headers)

#              print(str(res2.status_code) + " " + res2.reason)
        if (res2.status_code != 200):
            print(res2.text)

    stoptime = datetime.now()
    delta = stoptime - starttime
    print(datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "Runtime : " + '% 2d.%06d' %(delta.seconds,delta.microseconds))
