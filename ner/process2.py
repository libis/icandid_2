import spacy
import json
import requests
import datetime
import random
import os
import fcntl

requests.packages.urllib3.disable_warnings(requests.packages.urllib3.exceptions.InsecureRequestWarning)

esquery={
  "query": {
    "bool": {
      "should": [
        {
          "bool": {
            "must_not": [
              {
                "exists": {
                  "field": "_named_entities.generated"
                }
              }
            ]
          }
        },
        {
          "bool": {
            "must": [
              {
                "range": {
                  "_named_entities.generated": {
                    "lte": "2022-04-07T08:00:00"
                  }
                }
              }
            ]
          }
        }
      ]
    }
  },
  "_source": [
    "headline",
    "description",
    "articleBody",
    "text",
    "_named_entities",
    "_named_entities.generated"
  ],
  "sort": {
    "sdDatePublished": {
      "order": "desc"
    }
  },
  "size": 10000
}

euquery={
    "doc":{
        "_named_entities" : {
            "generated":"",
            "entities":[]
            }
    }
}

config = json.load(open('./.env2.json'))

models = config["models"]
elastic_url = config["elastic_url"] + config["elastic_index"] + '/_search?track_total_hits=true'
update_url = config["elastic_url"] + config["elastic_index"] + '/_update/'
user = config["elastic_user"]
passwd = config["elastic_password"]
regen_date = config["regen_date"]

headers = {"Content-Type":"application/json", "Accept":"application/json"}

fh=0
def run_once():
    global fh
    fh=open(os.path.realpath(__file__),'r')
    try:
        fcntl.flock(fh,fcntl.LOCK_EX|fcntl.LOCK_NB)
    except:
        print("Still busy so not going to start again");
        os._exit(0)

def findinout(text, label, l) :
    for i in range(len(l)):
        if (l[i]["value"] == text and l[i]["label"] == label):
            return i
    return 0


if __name__ == '__main__':

    run_once()

    nlp = {}

    starttime = datetime.datetime.now()
    esquery["query"]["bool"]["should"][1]["bool"]["must"][0]["range"]["_named_entities.generated"]["lte"] = regen_date

    res = requests.post(elastic_url, auth=(user, passwd), data=json.dumps(esquery), verify=False, headers=headers)

    data = json.loads(res.text)
#    print(json.dumps(data, indent=4, sort_keys=True))
    if (data["hits"]["total"]["value"] == 0):
        exit()

    random.shuffle(data["hits"]["hits"])

    for article in data["hits"]["hits"]:
        doc_id = article["_id"]
        print("NER : " + doc_id)        

        texts = {}

        euquery["doc"]["_named_entities"]["entities"] = []

        for f in ['headline','description','articleBody','text']:

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
                    nlp[lang] = spacy.load(models[lang])

                for f in ['headline','description','articleBody','text']:
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
            
                doc = nlp[lang](content)


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
                euquery["doc"]["_named_entities"]["entities"].append({ lang:out[:10000] })
        
        euquery["doc"]["_named_entities"]["generated"] = datetime.datetime.now().isoformat()
#        print(update_url + doc_id)
#        print(json.dumps(out, indent=4, sort_keys=True))
        res2 = requests.post(update_url + doc_id, auth=(user, passwd), data=json.dumps(euquery), verify=False, headers=headers)

#        print(str(res2.status_code) + " " + res2.reason)
#        if (res2.status_code != 200):
#        print(res2.text)

    stoptime = datetime.datetime.now()
    delta = stoptime - starttime
    print('% 2d.%06d' %(delta.seconds,delta.microseconds))
