import json
import requests
import os
import fcntl
import copy
import sys
import datetime
import queue
import threading
import time

requests.packages.urllib3.disable_warnings(requests.packages.urllib3.exceptions.InsecureRequestWarning)

config_file = './' + sys.argv[1]
print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "Opening configuration " + config_file)
config = json.load(open(config_file))
ner_service_url = config["ner_service_url"]
elastic_url = config["elastic_url"] + config["elastic_index"] + '/_search?track_total_hits=true'
elastic_docurl = config["elastic_url"] + config["elastic_index"] + '/_doc/'
update_url = config["elastic_url"] + config["elastic_index"] + '/_update/'
user = config["elastic_user"]
passwd = config["elastic_password"]
querydate = config["querydate"]
models = config["models"]
version = json.loads(requests.get(ner_service_url+"/version").text)
compatibility = json.loads(requests.get(ner_service_url+"/models").text)
esquery={
  "_source": [
      #"name", 
      "@id", 
      #"@type", "headline", "description", "articleBody", "text", "alternateName", "prov:wasAttributedTo", "inLanguage", "@context"
      ],
  "sort": { 
    "updatetime": {
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
#        "name",
        "@id",
#        "@type",
#        "headline",
#        "description",
#        "articleBody",
#        "text",
#        "alternateName",
#        "prov:wasAttributedTo",
#        "inLanguage",
#        "@context"
    ],
    "sort": {
        "updatetime": {"order": "desc"}
    },
    "size": 1,
    "query": {
        "bool": {
            "must": [
                {
                    "bool": {
                        "should": []
                    }
                },
                {
                    "bool": {
                        "should": []
                    }
                },
                {
                    "terms": {
                        "prov:wasAttributedTo.name.keyword": ["SpaCy"]
                    }
                },
                 
            ],
            "must_not": {
                "terms": {
                    "prov:wasAttributedTo.version.keyword": ["3.7.1"]
                }
            }
        }
    }
}

org_newblock = {
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
org_newblock["version"] = version["spaCy"]



org_newblock2 = {
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


esquery["size"] = config["querysize"]
esquery2["size"] = config["querysize"]
esquery2["query"]["bool"]["must_not"]["terms"]["prov:wasAttributedTo.version.keyword"] = [version["spaCy"]] 

headers = {"Content-Type":"application/json", "Accept":"application/json"}

process_fields = ['headline','description','articleBody','text','alternateName','name']

def findinout(text, label, l) :
    for i in range(len(l)):
        if (l[i]["value"] == text and l[i]["label"] == label):
            return i
    return 0

def stats():
  if (config["stats"] == False):
    return
  worker1count = len(list(filter(lambda w: w.is_alive(), workers_type1)))
  try:
    worker2count = len(list(filter(lambda w: w.is_alive(), workers_type2)))
  except NameError:
    worker2count = 0
  queue1size = queue1.qsize()
  queue2size = queue2.qsize()
  delta = datetime.datetime.now() - starttime
  runtime = str(datetime.timedelta(seconds=delta.seconds))
  print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "STA : " + f'worker1: {worker1count} - worker2 : {worker2count} - queue1 : {queue1size} - queue2: {queue2size} - Runtime : {runtime}')

def selector(queue):
  print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "Running query 1")
  res = requests.post(elastic_url, auth=(user, passwd), data=json.dumps(esquery), verify=False, headers=headers, timeout=360)
  data = json.loads(res.text)
  #print(json.dumps(data, indent=4, sort_keys=True))
  if (data["hits"]["total"]["value"] == 0):
    print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "Running query 2")
    res = requests.post(elastic_url, auth=(user, passwd), data=json.dumps(esquery2), verify=False, headers=headers, timeout=360)  
    data = json.loads(res.text)
    #print(json.dumps(data, indent=4, sort_keys=True))
    if (data["hits"]["total"]["value"] == 0):
      esquery["query"]["bool"]["must_not"][0]["nested"]["query"] = {"range": {"prov:wasAttributedTo.prov:wasAssociatedFor.prov:generatedAtTime": {"gte": querydate } } }
      print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "Running query 3")
      #print(esquery)
      res = requests.post(elastic_url, auth=(user, passwd), data=json.dumps(esquery), verify=False, headers=headers, timeout=360)
      data = json.loads(res.text)
      #print(json.dumps(data, indent=4, sort_keys=True))
      if (data["hits"]["total"]["value"] == 0):
          stoptime = datetime.datetime.now()
          delta = stoptime - starttime
          print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "Runtime : " + '% 2d.%06d' %(delta.seconds,delta.microseconds))            
          exit()

  print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "# records to do : " + str(data["hits"]["total"]["value"]))
  
  for article_id in data["hits"]["hits"]:
    res4 = requests.get(elastic_docurl + article_id["_id"], auth=(user,passwd), verify=False, headers=headers, timeout=360)
    article = json.loads(res4.text)
    doc_id = article["_id"]
    print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "REA : " + doc_id)
    queue1size = queue1.qsize()
    queue2size = queue2.qsize()
    delta = datetime.datetime.now() - starttime
    runtime = str(datetime.timedelta(seconds=delta.seconds))
    if (config["stats"]):
      print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "STA : " + f'queue1 : {queue1size} - queue2: {queue2size} - Runtime : {runtime}')


    texts = {}

    try:
      euquery = copy.deepcopy(article["_source"]["prov:wasAttributedTo"])
    except KeyError:
      euquery = []
    newblock = copy.deepcopy(org_newblock)
    newblock["prov:wasAssociatedFor"] = []

    # if spacy already exists remove it because we will update
    try:
        for i, x in reversed(list(enumerate(euquery))):
            if x["@id"] == "spacy":
                del(euquery[i])
    except KeyError:
        print(doc_id)
        quit()

    for f in process_fields:

      if f in article["_source"]:
        if (isinstance(article["_source"][f], list)):
          if (article["_source"][f][0] is not None):
            if article["_source"][f][0]["@language"] not in texts.keys():
              texts[article["_source"][f][0]["@language"]] = {}
            texts[article["_source"][f][0]["@language"]][f] = article["_source"][f][0]["@value"].replace("\r\n", " ")                 
        else:
          if (article["_source"][f] is not None):
            if (isinstance(article["_source"][f]["@language"], list)):
                for lang in article["_source"][f]["@language"]:
                    if lang not in texts.keys():
                        texts[lang] = {}
                    texts[lang][f] = article["_source"][f]["@value"].replace("\r\n", " ")
            else:
                if article["_source"][f]["@language"] not in texts.keys():
                    texts[article["_source"][f]["@language"]] = {}
                texts[article["_source"][f]["@language"]][f] = article["_source"][f]["@value"].replace("\r\n", " ") 

#    print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "NER : (A) " + doc_id)
    queue.put({"doc_id":doc_id,"texts":texts,"euquery":euquery,"newblock":newblock})


# ---------------- WorkerType1 ---------------- 
def worker_type1():
  while True:
    item = queue1.get()
    stats()
    if item is None:  # sentinel -> exit
      queue1.task_done()
      break
    euquery = copy.deepcopy(item["euquery"])
    doc_id = copy.deepcopy(item["doc_id"])
    print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "NER : " + doc_id + " " + str(len(json.dumps(item))))
    newblock = copy.deepcopy(item["newblock"])

    for lang,fields in item["texts"].items():
      fieldkeys = list(fields.keys())
      content = ""

      if lang in models.keys():
        for f in process_fields:
            if (f not in fields.keys()):
                fields[f] = ""

        if ((fields["headline"] not in fields["description"]) and (fields["headline"] not in fields["articleBody"]) and (fields["headline"] not in fields["text"])):
            content += fields["headline"] + " "
        if ((fields["description"] not in fields["articleBody"]) and (fields["description"] not in fields["text"])):
            content += fields["description"] + " "
        if (fields["text"] not in fields["articleBody"]):
            content += fields["text"] + " "

        content += fields["articleBody"]

        if (fields["name"] not in content):
            content = fields["name"] + " " + content + " "
        if (fields["alternateName"] not in content):
            content = fields["alternateName"] + " " + content + " "
        content = content.replace("\r\n", " ")
        if (content == ""):
            print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "NER : " + doc_id + " no content")
            ner_result = []
        else:
          try:
            res = requests.post(ner_service_url+"/entities",data=json.dumps({"text":content[0:1000000],"model":models[lang]["@id"]}),timeout=6000)
            
          except:
            print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "NER : " + doc_id + " failed")
            #print(json.dumps({"text":content[0:1000000],"model":models[lang]["@id"]}))
            continue
          
          try:
            ner_result = json.loads(res.text)
          except json.JSONDecodeError:
            #print(ner_result.text)
            continue  
            
        out = []

        for ent in ner_result:
            if (ent["label"] not in ["DATE", "DATETIME", "NUMERIC_VALUE", "DERIV_PER", "ORDINAL", "CARDINAL", "PERCENT", "TIME", "MONEY", "QUANTITY", "date", "time"]):
                idx = findinout(ent["text"], ent["label"], out);
                if (idx == 0):
                    new = {"value":ent["text"], "label":ent["label"], "count":1}
                    out.append(new)
                else: 
                    out[idx]["count"] += 1
        newblock2 = copy.deepcopy(org_newblock2)
        newblock2["prov:used"][0]["itemListElement"] = fieldkeys
        newblock2["prov:used"][1]["name"] = models[lang]["name"] + " pipeline"
        newblock2["prov:used"][1]["@id"] = models[lang]["@id"]
        newblock2["prov:used"][1]["version"] = compatibility[models[lang]["@id"]]["version"]
        newblock2["prov:used"][1]["url"] = models[lang]["url"]
        newblock2["prov:generatedAtTime"] = datetime.datetime.now().strftime("%Y-%m-%dT%H:%M:%S.%f")
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

#      print(json.dumps(euquery,indent=4))

    if (len(euquery) != 0):
      queue2.put({"doc_id":doc_id,"euquery":euquery})
#        print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "NER : (P) " + doc_id + f" {threading.current_thread().name}" + " " + str(queue2.qsize()))
    queue1.task_done()




def worker_type2():
  while True:
    data = queue2.get()
    stats()
    if data is None:  # sentinel -> exit
        queue2.task_done()
        break
    doc_id = data["doc_id"]
    euquery = data["euquery"]
    print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "SAV : " + doc_id + " " + str(len(json.dumps(data))))
    try:
      #print(json.dumps({"doc":{"prov:wasAttributedTo":euquery}}))
      res2 = requests.post(update_url + doc_id, auth=(user, passwd), data=json.dumps({"doc":{"prov:wasAttributedTo":euquery}}), verify=False, headers=headers, timeout=600)
      #time.sleep(0.01)
      #print(res2.text)
    except Exception as e:
      print(e)
      print(res2.text)
      print(update_url + doc_id)
      pass
    queue2.task_done()


# Source - https://stackoverflow.com/a
# Posted by MohitC, modified by community. See post 'Timeline' for change history # Retrieved 2025-12-18, License - CC BY-SA 4.0

def compare_dictionaries(dict_1, dict_2, dict_1_name, dict_2_name, path=""):
    """Compare two dictionaries recursively to find non matching elements

    Args:
        dict_1: dictionary 1
        dict_2: dictionary 2

    Returns: string

    """
    err = ''
    key_err = ''
    value_err = ''
    old_path = path
    for k in dict_1.keys():
        path = old_path + "[%s]" % k
        if not dict_2.has_key(k):
            key_err += "Key %s%s not in %s\n" % (dict_1_name, path, dict_2_name)
        else:
            if isinstance(dict_1[k], dict) and isinstance(dict_2[k], dict):
                err += compare_dictionaries(dict_1[k],dict_2[k],'d1','d2', path)
            else:
                if dict_1[k] != dict_2[k]:
                    value_err += "Value of %s%s (%s) not same as %s%s (%s)\n"\
                        % (dict_1_name, path, dict_1[k], dict_2_name, path, dict_2[k])

    for k in dict_2.keys():
        path = old_path + "[%s]" % k
        if not dict_1.has_key(k):
            key_err += "Key %s%s not in %s\n" % (dict_2_name, path, dict_1_name)

    return key_err + value_err + err



if __name__ == '__main__':

  starttime = datetime.datetime.now()

  for model in models:
    mod = {"term":{"prov:wasAttributedTo.prov:wasAssociatedFor.@id":"spacy_ner_"+models[model]["@id"]}}
    esquery["query"]["bool"]["must_not"][0]["nested"]["query"]["bool"]["should"].append(mod)
    for f in process_fields:
      esquery["query"]["bool"]["must"][1]["bool"]["should"].append({"exists": {"field": model+"_"+f}})
      esquery2["query"]["bool"]["must"][1]["bool"]["should"].append({"exists": {"field": model+"_"+f}})
  for f in process_fields:
    esquery["query"]["bool"]["must"][0]["bool"]["should"].append({"exists":{"field":f}})
    esquery2["query"]["bool"]["must"][0]["bool"]["should"].append({"exists":{"field":f}})

#    print(json.dumps(esquery, indent=4))

  # Create the queues
  queue1 = queue.Queue()
  queue2 = queue.Queue()


  try:
    NUM_WORKERS_TYPE1 = config["ner_workers"]
  except KeyError:
    NUM_WORKERS_TYPE1 = 1
  
  try: 
    NUM_WORKERS_TYPE2 = config["es_workers"]
  except KeyError:
    NUM_WORKERS_TYPE2 = 1


  # Create and start WorkerType1 threads
  print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "Create and start WorkerType1 threads")
  workers_type1 = []
  for _ in range(NUM_WORKERS_TYPE1):
    t = threading.Thread(target=worker_type1)
    t.start()
    workers_type1.append(t)

  selector(queue1)

  # Add sentinel values for WorkerType1 threads to stop gracefully
  print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "Add sentinel values for WorkerType1 threads to stop gracefully")
  for _ in range(len(list(filter(lambda w: w.is_alive(), workers_type1)))):
    queue1.put(None)

  # Create and start WorkerType2 threads
  print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "Create and start WorkerType2 threads")
  workers_type2 = []
  for _ in range(NUM_WORKERS_TYPE2):
    t = threading.Thread(target=worker_type2)
    t.start()
    workers_type2.append(t)

  # Wait for WorkerType1 to finish Queue1
  print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "Wait for WorkerType1 to finish Queue1")
  queue1.join()
 

  # Send sentinel to WorkerType2
  print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "Send sentinel to WorkerType2")
  for _ in range(len(list(filter(lambda w: w.is_alive(), workers_type2)))):
    queue2.put(None)
  stats()

  # Wait for WorkerType2 to finish
  print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "Wait for WorkerType2 to finish")
  queue2.join()

  # Wait for all threads to exit
  print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "Wait for all threads (1) to exit")
  for t in workers_type1:
    t.join()

  print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "Wait for all threads (2) to exit")
  for t in workers_type2:
    t.join()

  print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + "Done waiting")
  

  delta = datetime.datetime.now() - starttime
  runtime = str(datetime.timedelta(seconds=delta.seconds))
  print(datetime.datetime.now().strftime("[%Y-%m-%d %H:%M:%S] ") + f"Runtime : {runtime}")


