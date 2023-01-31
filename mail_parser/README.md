
# convert mailbox pst-format to mbox-format
=> docker-compose run --rm pst_converter /app/bin/convert.sh 
INDIR=/source_records/mails/inpit.pst
OUTDIR=/source_records/mails/pst_to_mbox
===> converteren van input.pst bestanden naar folderstuctuur met daarin een mbox

#  redis db  for anonymization off mail-addresses
docker-compose run --rm mailbox_parser python /app/src/init_redis.py

# parser mails from mbox to schema.org-json-files
docker-compose run --rm mailbox_parser python /app/src/mbox_parser.py -h
docker-compose run --rm mailbox_parser python /app/src/mbox_parser.py -s /source_records/mails/mails_mails_query_0000001/mail.mbox -d /records/mails/mails_mails_query_0000001/



