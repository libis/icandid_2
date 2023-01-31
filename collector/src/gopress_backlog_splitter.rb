#encoding: UTF-8
$LOAD_PATH << '.' << './lib' << "#{File.dirname(__FILE__)}" << "#{File.dirname(__FILE__)}/lib"
require "unicode"
require 'logger'
require 'icandid'
require 'xml_split'

require_relative './rules/gopress_backlog_v1.0'
# require 'belgapress_utils'

################################################################################################
# Om de backlog records te parsen, moet de parameter -s in de commandline worden gebruikt
#  => ruby ./src/gopress_parser.rb -s /source_records/GoPress/backlog/ -u "2010-01-01T01:00:00Z" -p "*.xml.gz"
#
# 
# Om de "backlog" van recente records te parsn, gebruik commandline parameters
#  => ruby ./src/gopress_parser.rb -s /source_records/GoPress/**/*.202202*.zip -u "2010-01-01T01:00:00Z"
#

include Icandid

@logger = Logger.new(STDOUT)
@logger.level = Logger::DEBUG

ADMIN_MAIL_ADDRESS = "tom.vanmechelen@kuleuven.be"
ROOT_PATH = File.join( File.dirname(__FILE__), '../')

# ConfJson = File.read( File.join(ROOT_PATH, './config/config.cfg') )
# ICANDID_CONF = JSON.parse(ConfJson, :symbolize_names => true)
PROCESS_TYPE  = "parser"  # used to determine (command line) config options
SOURCE_DIR   = '/source_records/GoPress/'
SOURCE_FILE_NAME_PATTERN = "*.zip"

RECORDS_DIR  = '/records/GoPress/'

ConfJson = File.read(File.join(ROOT_PATH, './config/config.cfg'))
ICANDID_CONF = JSON.parse(ConfJson, :symbolize_names => true)

ingestConfJson =  File.read(File.join(ROOT_PATH, './config/GoPress/ingest.cfg'))
INGEST_CONF = JSON.parse(ingestConfJson, :symbolize_names => true)

INGEST_CONF[:prefixid] = ICANDID_CONF[:prefixid]
INGEST_CONF[:genericRecordDesc] = "Entry from #{INGEST_CONF[:dataset][:name]}"

#############################################################
TESTING = true
STATUS = "parsing"

begin
  config = {
    :config_path => File.join(ROOT_PATH, './config/GoPress/'),
    :config_file => "config.yml",
    :query_config_path => File.join(ROOT_PATH, './config/GoPress/'),
    :query_config_file => "queries.yml"
  }

  icandid_config = Icandid::Config.new( config: config )
  
  collector = IcandidCollector::Input.new( icandid_config.config )

  @logger.info ("Start parsing using config: #{ icandid_config.config.path}/#{ icandid_config.config.file} ")

  start_process  = Time.now.strftime("%Y-%m-%dT%H:%M:%SZ")
  
  @logger.info ("Parsing for queries in : #{ icandid_config.query_config.path }#{ icandid_config.query_config.file }")

  options = {
    :type => "NewsArticle",
    :prefixid => "#{INGEST_CONF[:prefixid]}_#{ INGEST_CONF[:provider][:@id].downcase }_#{ INGEST_CONF[:dataset][:@id].downcase }",
    :papers_to_process => ["demorgen","destandaard","detijd","gazetvanantwerpen","hetbelangvanlimburg","hetlaatstenieuws","hetnieuwsblad","metro","metronl","metrofr"]
  }

  rule_set =  BACKLOG_RULE_SET_v1_0

  extract_destination = "#{icandid_config.config[:extraction_dir]}/#{ Time.now.utc.strftime("%Y%m%d%H%M%S") }"
  FileUtils.mkdir_p(extract_destination)

  @logger.info("Deleting extract_destination #{extract_destination}*")
  Dir.glob("#{extract_destination}/*").each { |file| File.delete(file) }

  source_records_dir = icandid_config.get_source_records_dir( options: {})
#  last_parsing_datetime = icandid_config.get_parsing_datetime( query: query )
  source_file_name_pattern = icandid_config.get_file_name_pattern()
  
  @logger.info ("Start parsing source_records_dir: #{source_records_dir} ")
  @logger.info ("Start parsing source_file_name_pattern: #{source_file_name_pattern}")

  Dir.glob("#{ source_records_dir }#{ source_file_name_pattern }").each do |z_file|

    @logger.debug("#{ z_file } ctime(z_file) #{ File.ctime(z_file) } ")
    @logger.debug("#{ z_file } mtime(z_file) #{ File.mtime(z_file) } ")
    @logger.debug("extract  #{ z_file } ")


    puts "gopress-2017-12.xml"
    source_file = "/source_records/GoPress/xml_extract//20220613070946/gopress-2017-12.xml"

    queries_to_parse = []
    icandid_config.query_config[:queries].each.with_index() do |query, index|
      @logger.info ("Paring records for query: #{ query[:query][:id] } [ #{ query[:query][:name] } ]")
      file_to_parse = "#{source_file}-#{ query[:query][:value].downcase }.json"
      File.open(file_to_parse, 'w') { |f| f.write( "{\"news\": { \"text\": [") }
      queries_to_parse <<  query[:query][:value].downcase
    end

   @logger.info (" parsing #{source_file}")
   x = XmlSplit.new(source_file, 'text')
   x.each_with_index { |node,i|
      h_node = Hash.from_xml(node) 
      file_to_add_record = h_node["text"]["product"].gsub(/[[:space:]]/, '').downcase
      if queries_to_parse.include?(file_to_add_record)
        file_to_add_record = "#{source_file}-#{ file_to_add_record }.json"
        File.open(file_to_add_record, 'a') { |f| f.write( "#{h_node["text"].to_json}," ) }
      end

   } 

    icandid_config.query_config[:queries].each.with_index() do |query, index|
      file_to_parse = "#{source_file}-#{ query[:query][:value].downcase }.json"
      File.open(file_to_parse, 'a') { |f| f.write( "{}]}}") }
    end


    icandid_config.query_config[:queries].each.with_index() do |query, index|
      file_to_parse = "#{source_file}-#{ query[:query][:value].downcase }.json"
      data = input.from_uri("file://#{ file_to_parse }", {} )
    end


   puts x.class
   @logger.info ("-----------------------------------------------")

    doc = input.from_uri("file://#{ source_file }", {} )

    icandid_config.query_config[:queries].each.with_index() do |query, index|
      @logger.info ("Paring records for query: #{ query[:query][:id] } [ #{ query[:query][:name] } ]")
         doc["news"]["text"].select { |d|  
          if d["product"].nil?
            puts d["product"]
            puts d
          end
          ! d["product"].nil? && ( query[:query][:value].downcase == d["product"].gsub(/[[:space:]]/, '').downcase )
        }.each_slice(100).with_index do |d, i|
          d = {
            "news" => {
              "text" => d
            }
          }
	  
	  puts "-slice--"
          files_to_parse = "#{source_file}-#{ query[:query][:value].downcase }.json"
          File.open(files_to_parse, 'w') {|f| f.write( d.to_json ) }

        end
    end



exit

doc = input.from_uri("file://#{ source_file }", {} )
puts doc.class
puts doc.keys
puts "-------------"
    puts "gopress-2017-11.xml"
source_file = "/source_records/GoPress/xml_extract//20220613070946/gopress-2017-11.xml"
doc = input.from_uri("file://#{ source_file }", {} )
puts doc.class
puts doc.keys

exit
    puts "gopress-2017-12.xml"
source_file = "/source_records/GoPress/xml_extract//20220613070946/gopress-2017-12.xml"
doc = input.from_uri("file://#{ source_file }", {} )
puts doc.class
puts doc.keys

#doc.keys.each { |k|
# puts "-#{k}"
# puts "  -#{doc[k].keys}"
#}

exit

    if  File.extname( z_file )  == ".gz"
      f_path = File.join(extract_destination, File.basename(z_file, '.gz'))
      Zlib::GzipReader.open(z_file) do | input_stream |
        File.open(f_path, "w") do |output_stream|
          IO.copy_stream(input_stream, output_stream)
        end
      end
    end


exit
    Dir["#{extract_destination}/*.xml"].each.with_index do |source_file, index| 

      doc = input.from_uri("file://#{ source_file }", {} )

      icandid_config.query_config[:queries].each.with_index() do |query, index|
        @logger.info ("Paring records for query: #{ query[:query][:id] } [ #{ query[:query][:name] } ]")

        INGEST_CONF[:dataset][:@id]  = query[:query][:id]
        INGEST_CONF[:dataset][:name] = query[:query][:name].gsub(/_/," ").capitalize()

        dir_options = { 
          :query =>  query[:query], 
          :date => Date.today.strftime("%Y/%m/%d")
        }

puts "QQQQQQQQQQQQQQQQQQQQQ #{query[:query]}"
puts "QQQQQQQQQQQQQQQQQQQQQ #{query[:query][:value]}"
puts doc.raw.keys
doc.keys.each { |k|
 puts "-#{k}"
 puts "  -#{doc[k].keys}"
}

        File.open("#{source_file}-#{ query[:query][:value].downcase }.json", 'w') { |f| 
		f.write( 
                   doc["news"]["text"].select { |d|  
                       if d["product"].nil?
                          puts d["product"]
                          puts d
                       end
                       ! d["product"].nil? && ( query[:query][:value].downcase == d["product"].gsub(/[[:space:]]/, '').downcase )
	           }.to_json
		) 
	}
      end
    end

    exit

    @logger.info("Deleting extract_destination #{extract_destination}*")
    Dir.glob("#{extract_destination}/*").each { |file| File.delete(file) }

  end

#  FileUtils.rm_rf(extract_destination)

rescue StandardError => e
  @logger.error("#{ e.message  }")
  @logger.error("#{ e.backtrace.inspect   }")

  importance = "High"
  subject = "[ERROR] iCANDID GoPress backlog parsing"
  message = <<END_OF_MESSAGE
  
  <h2>Error while parsing GoPress backlog data</h2>
  <p>#{e.message}</p>
  <p>#{e.backtrace.inspect}</p>
  
  <hr>
  
END_OF_MESSAGE

  Icandid::Utils.mailErrorReport(subject, message, importance, config)

  @logger.info("GoPress Backlog Parsing is finished with errors")

end
