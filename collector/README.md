## twitter_download.rb
# docker-compose run --rm collector ruby ./src/rules/twitter_download.rb
# options:
-c : yml-file (including path) with the configuration (./config/config.yml) 
-q : yml-file (including path) with twitter query configuration (./config/queries.yml)
-s : Directory where the records will be stored (/source_records/twitter/)
-u : Time the command was last run (have no effect on the API-query !!!)

# config.yml
this file contains the twitter-developr-account parameters
source_records_dir, records_dir, source_file_name_pattern, rule_set

# queries.yml 
The queries-file is the configuration of all the different twitter request.  
If the api_request equals "all" and the status is completed, the query is not processed.  
newest_id and oldest_id will be used in the "resent_search" request. 
v2_start_time and v2_end_time will be used in the "all" request.  

# source_records_dir (Directory where the records will be stored)
placeholders that can be used the source_dir  
- today
- api_request (all / Recent_search)
- api_version (mostly v2)
- query_id (if this placeholder is missing the query_id is added to the end of the source_dir)

# examples
- ruby ./src/twitter_download.rb -q ./config/twitter/user_queries.yml
- ruby ./src/twitter_download.rb -c ./config/twitter/config.yml

docker-compose run --rm collector ruby ./src/twitter_download.rb -q ./config/twitter/query_manual.yml 

docker-compose run --rm collector ruby ./src/twitter_download.rb -c ./config/twitter/config_v2_all.yml -q ./config/twitter/user_queries.yml 



### TEST
docker-compose run --rm collector ruby ./src/twitter_download.rb -c ./config/twitter/config.yml -q ./config/twitter/european_politics_user_queries.yml -s /source_records/twitter/v2/european_politics/
docker-compose run --rm collector ruby ./src/twitter_download.rb -c ./config/twitter/config_v2_all_ruleset2_3.yml -q ./config/twitter/queries.yml -s /source_records/Twitter/v2/test/


## twitter_parser_v2.rb
# docker-compose run --rm collector ruby ./src/rules/twitter_parser_v2.rb (to convert the records from twitter-json to schema.org)
# options:
-l : file to write log
-c : yml-file (including path) with the configuration (./config/config.yml)"
-i : Ingest config file
-q : File with twitter query configuration relative to config path
-s : Directory of the source records (/source_records/twitter/") 
-d : Directory to save the parsed schema.org json-ld records (/records/Twitter/{{query_id}})"
-p : file pattern of record-filename /twitter*\\\.json/
-u : Time the command was last run
-b : Make subfolders in destination-directory (-d) based on DatePublished in the record (true/false)
-n : The id of the query if only one specific query needs to be parsed

# placeholders in source_directory and destination_directory
- today
- api_request (all / Recent_search)
- api_version (mostly v2)
- query_id (if this placeholder is missing the query_id is added to the end of the source_dir)

# placeholders in destination_directory
- query_v2_start_time
- query_v2_end_time

# Example
- ruby ./src/twitter_parser_v2.rb -u '2021-03-15 16:00'
- ruby ./src/twitter_parser_v2.rb -c ./config/twitter/config.yml  -s "./source_records/Twitter/v2/Recent_search/**/"  -p "twitter*.json"  -q queries.yml  -u "2021-03-01 21:18:19"
- ruby ./src/twitter_parser_v2.rb -c ./config/twitter/config_v2_all.yml -q ./config/twitter/queries.yml -s "./source_records/Twitter/v2/all/2021_04_20/"  -p "twitter*.json" -u "2021-04-10 08:28:23 +0200"

- Daily/Weekly updates (updates recent_search starting from newest_id (tweet-id) in queries.yml)
docker-compose run --rm collector ruby ./src/twitter_download.rb -c ./config/twitter/config.yml -q ./config/twitter/queries.yml
docker-compose run --rm collector ruby ./src/twitter_parser_v2.rb -c ./config/twitter/config.yml -q ./config/twitter/queries.yml

docker-compose run --rm collector ruby ./src/twitter_parser_v2.rb -c ./config/twitter/config_v2.yml -q ./config/twitter/queries.yml
docker-compose run --rm collector ruby ./src/twitter_parser_v2.rb -c ./config/twitter/config_v2.yml -q ./config/twitter/user_queries.yml
docker-compose run --rm collector ruby ./src/twitter_parser_v2.rb -c ./config/twitter/config_v2.yml -q ./config/twitter/european_politics_user_queries.yml
docker-compose run --rm collector ruby ./src/twitter_parser_v2.rb -c ./config/twitter/config_v2.yml -q ./config/twitter/vlaamse_politics_user_queries.yml
docker-compose run --rm collector ruby ./src/belgapress_parser.rb -c ./config/BelgaPress/config.yml -q ./config/BelgaPress/queries.yml -u "2021-03-11 1:00:00" 


docker-compose run --rm collector ruby ./src/belgapress_parser.rb -c ./config/BelgaPress/config.yml -q ./config/BelgaPress/queries.yml -u "2021-03-11 1:00:00" -s /source_records/BelgaPress/{{query_name}}/2022_03/12/

/usr/local/bin/docker-compose -f docker-compose.yml run --rm  icandid_collector ruby ./src/belgapress_parser.rb -c ./config/BelgaPress/config.yml -q ./config/BelgaPress/queries.yml -u "2021-03-11 1:00:00" -s /source_records/BelgaPress/{{query_name}}/2022_03/12/