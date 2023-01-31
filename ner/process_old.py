import spacy
import json
import requests
import datetime
import random

requests.packages.urllib3.disable_warnings(requests.packages.urllib3.exceptions.InsecureRequestWarning)

esquery={
    "query": {
        "function_score": {
            "query": {
                "bool": {
                    "must_not": {
                        "term": {
                            "_named_entities.model": "nl_core_news_lg"
                        }
                    }
                }
            },
            "boost": 5,
            "random_score": {},
            "boost_mode": "multiply"
        }
    },
    "_source": [
        "headline",
        "description",
        "articleBody",
        "_named_entities"
    ],
    "size": 10000
}


euquery={
    "doc":{
        "_named_entities" : {
            "model":"",
            "entities":[]
            }
    }
}

model = "nl_core_news_lg"

esquery["query"]["function_score"]["query"]["bool"]["must_not"]["term"]["_named_entities.model"] = model
euquery["doc"]["_named_entities"]["model"] = model

elastic_url = 'https://libis-p-icandid-2.lnx.icts.kuleuven.be:9200/icandid/_search?track_total_hits=true'
user = "icandid_admin"
passwd = "ibandid_admin"

update_url = 'https://libis-p-icandid-2.lnx.icts.kuleuven.be:9200/icandid/_update/'

headers = {"Content-Type":"application/json", "Accept":"application/json"}

for k in range(5):

    starttime = datetime.datetime.now()

    res = requests.post(elastic_url, auth=(user, passwd), data=json.dumps(esquery), verify=False, headers=headers)

    data = json.loads(res.text)

    if (data["hits"]["total"]["value"] == 0):
        exit()

    nlp = spacy.load(model)

    random.shuffle(data["hits"]["hits"])

    for article in data["hits"]["hits"]:

        text = ""
        if 'headline' in article["_source"]:
            text += article["_source"]["headline"] + " " 
        if 'description' in article["_source"]:
            text += article["_source"]["description"] + " " 
        if 'articleBody' in article["_source"]:
            text += article["_source"]["articleBody"] + " " 
        text = text.replace("\r\n", " ")
    
        doc_id = article["_id"]
        
    
        print("NER : " + doc_id)
    
        doc = nlp(text)
    
        out = []
        out2 = []

        for ent in doc.ents:
            if (ent.label_ != "DATE" and ent.label_ != "ORDINAL" and ent.label_ != "CARDINAL" and ent.label_ != "PERCENT" and ent.label_ != "TIME" and ent.label_ != "MONEY" and ent.label_ != "QUANTITY"):
                new = {"text":ent.text, "label":ent.label_}
                if new not in out:
                    out.append(new)
        # unieke entities -> dubbels verwijderen (unieke behouden eigenlijk)

        euquery["doc"]["_named_entities"]["entities"] = out

#       print(update_url + doc_id)
#       print(json.dumps(euquery, indent=4, sort_keys=True))
        res2 = requests.post(update_url + doc_id, auth=(user, passwd), data=json.dumps(euquery), verify=False, headers=headers)
#       print(res2.text)

    stoptime = datetime.datetime.now()
    delta = stoptime - starttime
#    print('% 2d.%06d' %(delta.seconds,delta.microseconds))

