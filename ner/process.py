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
            }
        }
    },
    "_source": [
        "headline",
        "description",
        "articleBody",
        "text",
        "_named_entities"
    ],
    "sort": {
      "sdDatePublished": {"order" : "desc"}
    },
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

config = json.load(open('./.env.json'))

model = config["model"]
elastic_url = config["elastic_url"] + '/icandid/_search?track_total_hits=true'
update_url = config["elastic_url"] + '/icandid/_update/'
user = config["elastic_user"]
passwd = config["elastic_password"]

esquery["query"]["function_score"]["query"]["bool"]["must_not"]["term"]["_named_entities.model"] = model + "_"
euquery["doc"]["_named_entities"]["model"] = model + "_"

headers = {"Content-Type":"application/json", "Accept":"application/json"}

def findinout(text, label, l) :
    for i in range(len(l)):
        if (l[i]["text"] == text and l[i]["label"] == label):
            return i
    return 0


for k in range(4):

    starttime = datetime.datetime.now()

    res = requests.post(elastic_url, auth=(user, passwd), data=json.dumps(esquery), verify=False, headers=headers)

    data = json.loads(res.text)

    if (data["hits"]["total"]["value"] == 0):
        exit()

    nlp = spacy.load(model)

    random.shuffle(data["hits"]["hits"])

    for article in data["hits"]["hits"]:

        headline = description = articleBody = text = content = ""
        if 'headline' in article["_source"]:
            headline = article["_source"]["headline"].replace("\r\n", " ") 
        if 'description' in article["_source"]:
            description = article["_source"]["description"].replace("\r\n", " ") 

        if 'articleBody' in article["_source"]:
            articleBody = article["_source"]["articleBody"].replace("\r\n", " ")

        if 'text' in article["_source"]:
            text = article["_source"]["text"].replace("\r\n", " ")


        if ((headline not in description) and (headline not in description) and (headline not in articleBody) and (headline not in text)):
            content += headline + " "
        if ((description not in articleBody) and (description not in articleBody)):
            content += description + " "
        if (text not in articleBody):
            content += text + " "

        content += articleBody

        content = content.replace("\r\n", " ")
    
        doc_id = article["_id"]
        
    
        print("NER : " + doc_id)
    
        doc = nlp(content)
    
        out = []


        for ent in doc.ents:
            if (ent.label_ != "DATE" and ent.label_ != "ORDINAL" and ent.label_ != "CARDINAL" and ent.label_ != "PERCENT" and ent.label_ != "TIME" and ent.label_ != "MONEY" and ent.label_ != "QUANTITY"):
                idx = findinout(ent.text, ent.label_, out);
                if (idx == 0):
                    new = {"text":ent.text, "label":ent.label_, "count":1}
                    out.append(new)
                else: 
                    out[idx]["count"] += 1

        euquery["doc"]["_named_entities"]["entities"] = out[:10000]
        

#       print(update_url + doc_id)
#        print(json.dumps(euquery, indent=4, sort_keys=True))

        res2 = requests.post(update_url + doc_id, auth=(user, passwd), data=json.dumps(euquery), verify=False, headers=headers)

#       print(res2.text)

    stoptime = datetime.datetime.now()
    delta = stoptime - starttime
#    print('% 2d.%06d' %(delta.seconds,delta.microseconds))

