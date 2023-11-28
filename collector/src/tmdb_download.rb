#encoding: UTF-8
$LOAD_PATH << '.' << './lib' << "#{File.dirname(__FILE__)}" << "#{File.dirname(__FILE__)}/lib"
require "unicode"
require 'logger'
require 'icandid'

include Icandid

@logger = Logger.new(STDOUT)
@logger.level = Logger::DEBUG

ROOT_PATH = File.join( File.dirname(__FILE__), '../')

# ConfJson = File.read( File.join(ROOT_PATH, './config/config.cfg') )
# ICANDID_CONF = JSON.parse(ConfJson, :symbolize_names => true)
PROCESS_TYPE  = "download"  # used to determine (command line) config options
SOURCE_DIR   = '/source_records/TMDB/'
RECORDS_DIR  = '/records/TMDB/'

#############################################################
TESTING = false
STATUS = "downloading"

COUNT = 100 

START_YEAR = 1945
END_YEAR = 2024

begin
  config = {
    :config_path => File.join(ROOT_PATH, './config/TMDB/'),
    :config_file => "config.yml",
    :query_config_path => File.join(ROOT_PATH, './config/TMDB/'),
    :query_config_file => "queries.yml"
  }

  icandid_config = Icandid::Config.new( config: config )
 
  collector = IcandidCollector::Input.new( icandid_config.config ) 

  @logger.info ("Start downloading using config: #{ icandid_config.config.path}/#{ icandid_config.config.file} ")

  start_process  = Time.now.strftime("%Y-%m-%dT%H:%M:%SZ")
  
  @logger.info ("downloading for queries in : #{ icandid_config.query_config.path }#{ icandid_config.query_config.file }")

  # Alwyas get the recent records first. After that start processing the backlog
  # All query[:recent_records][:url] are nil and all query[:recent_records][:last_run_update] have te value today: recent_records has been processed for today 
  url_options = {
    :base_url          => icandid_config.config[:base_url],
    :api_key      => icandid_config.config[:auth][:api_key]
  }

  ################## RECENT SEARCH ######################################
  icandid_config.query_config[:queries].each.with_index() do |query, index|

    @logger.info ("downloading recent_records for query: #{ query[:query][:id] } [ #{ query[:query][:name] } ]")
    if query[:recent_records].nil?
      # No records for this query
      @logger.info ("recent_records not configured for this query")
      next
    end

    # recent_records are downloaded to {{query_name}}/{{date}}/" 
    # - query_name is tanslitarted from query[:query][:name]
    # - date is download day (today) %Y_%m/%d
    options = { :collection_type => "recent_records", :query =>  query[:query] }
    source_records_dir = icandid_config.get_source_records_dir( options: options)

    @logger.info ("downloads written to #{ source_records_dir }")

    current_process_date = Time.now.strftime("%Y-%m-%d")

    for year in START_YEAR..END_YEAR do    

        url_options[:page] = 1
        url_options[:primary_release_date_gte]     = year.to_s + "-01-01"
        url_options[:primary_release_date_lte]	= year.to_s + "-12-31"

        url = icandid_config::create_url( url: icandid_config.config[:discover_url], query: query, options: url_options)


        data = collector.get_data(url, url_options)
        collector.retries = 0

        if data.nil?
            @logger.warn "NO DATA AVAILABLE on this url #{url}"
            break
        end

        pagecount = data["total_pages"]
        currentpage = 1

        while currentpage <= pagecount
            unless (data["results"].empty?)
                @logger.debug ("total record for this query : #{ data["total_results"]}")
                # Expand resultsdata to records with body
                data["results"].each{ |d|
                    url_options[:movie_id] = d["id"]
                    record_url =  icandid_config::create_record_url( url: icandid_config.config[:record_url], query: query, options: url_options)
                    record_data = collector.get_data(record_url, url_options)
                    collector.retries = 0
                    unless record_data.nil? || record_data.empty?
                        record_data = record_data.compact
                        filename = "#{d["id"]}"
                        @logger.info("Writing to #{filename}")
                        output.to_jsonfile(record_data, filename, source_records_dir, true)
                        sleep(1)
                        exit if TESTING
                    end
                }   
            end
            currentpage = currentpage + 1
            url_options[:page] = currentpage
            url = icandid_config::create_url( url: icandid_config.config[:discover_url], query: query, options: url_options) 
            data = collector.get_data(url, url_options)
            collector.retries = 0
        end 
    end

    


    query[:recent_records][:last_run_update] = start_process
    icandid_config::update_query_config(query: query, index: index)
  end
end





