import spacy
import json
import requests
import datetime
import random
from threading import Thread, Lock, current_thread
from queue import Queue
import os
import sys
import fcntl

requests.packages.urllib3.disable_warnings(requests.packages.urllib3.exceptions.InsecureRequestWarning)

esquery={
    "query": {
        "function_score": {
            "query": {
                "bool": {
                    "must_not": {
                        "term": {
                            "_named_entities.model": ""
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

model_suffix = "_"

model = config["model"]
elastic_url = config["elastic_url"] + '/icandid/_search?track_total_hits=true'
update_url = config["elastic_url"] + '/icandid/_update/'
user = config["elastic_user"]
passwd = config["elastic_password"]

esquery["query"]["function_score"]["query"]["bool"]["must_not"]["term"]["_named_entities.model"] = model + model_suffix
euquery["doc"]["_named_entities"]["model"] = model + model_suffix

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
        if (l[i]["text"] == text and l[i]["label"] == label):
            return i
    return 0

def worker(q, lock):
    global model,user,password,euquery,headers
    nlp = spacy.load(model)
    while True:
        article= q.get()  # blocks until the item is available

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

        with lock:
#            print(q.qsize())
            print("NER : " + doc_id)

        doc = nlp(content)

#        with lock:
#            print("NER : " + doc_id + " a")

        out = []

        for ent in doc.ents:
            if (ent.label_ != "DATE" and ent.label_ != "ORDINAL" and ent.label_ != "CARDINAL" and ent.label_ != "PERCENT" and ent.label_ != "TIME" and ent.label_ != "MONEY" and ent.label_ != "QUANTITY" and not ("http" in ent.text)):
                idx = findinout(ent.text, ent.label_, out);
                if (idx == 0):
                    new = {"text":ent.text.replace("\n",""), "label":ent.label_, "count":1}
                    out.append(new)
                else:
                    out[idx]["count"] += 1

        euquery["doc"]["_named_entities"]["entities"] = out[:10000]
#        with lock:
#            print("NER : " + doc_id + " b")

        res2 = requests.post(update_url + doc_id, auth=(user, passwd), data=json.dumps(euquery), verify=False, headers=headers)

#        with lock:
#            print("NER : " + doc_id + " c " + str(res2.status_code))
#            print(res2.text)

        q.task_done()

#        with lock:
#            print("NER : " + doc_id + " d")
        

if __name__ == '__main__':

    run_once()

    starttime = datetime.datetime.now()

    res = requests.post(elastic_url, auth=(user, passwd), data=json.dumps(esquery), verify=False, headers=headers)

    data = json.loads(res.text)

    if (data["hits"]["total"]["value"] == 0):
        exit()

    q = Queue()
    num_threads = 4
    lock = Lock()

    for article in data["hits"]["hits"]:
        q.put(article)


    for i in range(num_threads):
        t = Thread(name=f"Thread{i+1}", target=worker, args=(q, lock))
        t.daemon = True  # dies when the main thread dies
        t.start()

    q.join()

    stoptime = datetime.datetime.now()
    delta = stoptime - starttime
    print('NER : % 2d.%06d' %(delta.seconds,delta.microseconds))


