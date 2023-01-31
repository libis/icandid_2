import json
import redis
import hashlib
import yaml
import os
import sys

mails_prefill = {
    "erika.vlieghe@uantwerpen.be": "PERSON_0000000001",
	"pierre.vandamme@uantwerpen.be": "PERSON_0000000002",
    "marc.vanranst@uzleuven.be": "PERSON_0000000003",
    "steven.vangucht@sciensano.be": "PERSON_0000000004",
    "steven.vangucht@ugent.be": "PERSON_0000000004",
    "geert.molenberghs@kuleuven.be": "PERSON_0000000005",
    "geert.molenberghs@uhasselt.be": "PERSON_0000000005",
    "info@sciensano.be": "PERSON_0000000007",
    "test0008": "PERSON_0000000008",
    "test0009": "PERSON_0000000009",
    "test0010": "PERSON_0000000010",
    "test0011": "PERSON_0000000011",
    "test0012": "PERSON_0000000012",
    "test0013": "PERSON_0000000013",
    "test0014": "PERSON_0000000014",
    "test0015": "PERSON_0000000015",
    "test0016": "PERSON_0000000016",
    "test0017": "PERSON_0000000017",
    "test0018": "PERSON_0000000018",
    "test0019": "PERSON_0000000019",
    "test0020": "PERSON_0000000020"
}



def read_config(file_path):
    with open(file_path, "r") as f:
        return yaml.safe_load(f)

def query_yes_no(question, default="no"):
    """Ask a yes/no question via raw_input() and return their answer.

    "question" is a string that is presented to the user.
    "default" is the presumed answer if the user just hits <Enter>.
            It must be "yes" (the default), "no" or None (meaning
            an answer is required of the user).

    The "answer" return value is True for "yes" or False for "no".
    """
    valid = {"yes": True, "y": True, "ye": True, "no": False, "n": False}
    if default is None:
        prompt = " [y/n] "
    elif default == "yes":
        prompt = " [Y/n] "
    elif default == "no":
        prompt = " [y/N] "
    else:
        raise ValueError("invalid default answer: '%s'" % default)

    while True:
        sys.stdout.write(question + prompt)
        choice = input().lower()
        if default is not None and choice == "":
            return valid[default]
        elif choice in valid:
            return valid[choice]
        else:
            sys.stdout.write("Please respond with 'yes' or 'no' " "(or 'y' or 'n').\n")


def  prefill_with_values(redis_db, mails_prefill):
    for mail in mails_prefill:
        print(mails_prefill[mail], '->', mail )
        sha256_mail = hashlib.sha256( " ".join( mail.split() ).encode('utf-8') ).hexdigest()
        redis_db.mset({sha256_mail: json.dumps( { "id" : mails_prefill[mail] , "mail": mail } ) })

config_file = "/app/config/config.yml"
config = read_config(  os.path.join( os.path.abspath(os.path.dirname(__file__)), config_file) )

# print (config)
# print (config['REDIS'])


if query_yes_no(f"Clear/refill this redis database (host={config['REDIS']['HOST']}, port={config['REDIS']['PORT']}, db={config['REDIS']['DB']}) ?"):
    redis_db = redis.Redis(
        host=config['REDIS']['HOST'],
        port=config['REDIS']['PORT'],
        db=config['REDIS']['DB'])

    redis_db.flushdb()

    prefill_with_values(redis_db, mails_prefill)
    




    