#encoding: UTF-8
$LOAD_PATH << '.' << './lib'

require 'net/smtp'
require 'logger'
require 'fileutils'
require 'json'
require 'pp'
require 'date'

#def config
#    @config ||= ConfigFile
#end

ONLY_ONE_VALUE_ALLOWED = ["name","headline","articleBody","description"]

def default_options
  { 
    :config_file         => "es_loader_conf.yml",
    :log                 => "es_loader.log",
    :last_run_updates    => '2000-01-01T11:11:11+01:00',
    :max_records_per_file => 300,
    :full_reload         => false,
    :load_type           => "update",
    :record_dirs_to_load => nil,
    :record_pattern      => nil,
    :es_url              => nil,
    :es_version          => nil,
    :es_index            => nil,
    :es_pipeline_id      => nil,
    :import_mappings     => "/elastic/mappings.json",
    :import_settings     => "/elastic/settings.json",
    :import_pipeline     => "/elastic/pipeline.json"
  }
end

def  checkLangauge( jsondata, field_path, lang )
    fields = field_path.split('.')
    if fields.first == fields.last
        jsondata[ field_path ] = checkFieldLangauge( jsondata[ field_path ] , lang  )
    else
        field =  fields.shift 
        field_path =  fields.join('.') 
        data = jsondata[field]
        if ! data.nil? 
            if data.is_a?(Array)
                #data.map! { |d| checkLangauge( d, field_path, lang ) }
                data.each { |d| 
                    checkLangauge( d, field_path, lang )
                }
            else
                checkLangauge( data, field_path, lang )
            end
        end
    end
end

def  checkFieldLangauge( field, lang  )
    if field.is_a? String 
        x = field
        field = { "@value" => x , "@language" => "#{ lang }" }
    else
        if field.is_a?(Array)
            field =  field.map { |x| 
                if x.is_a? String 
                { "@value" => x , "@language" => "#{ lang }" } 
                else x 
                end
            }
        end
    end
    field
end

def  checkPersonLangauge( person, lang  )
    if  person['name'].is_a? String 
        person['name']  = { "@value" => person['name'] , "@language" => "#{ lang }" } 
    end
    if  person['familyName'].is_a? String 
        person['familyName']  = { "@value" => person['familyName'] , "@language" => "#{ lang }" } 
    end
    person
end


def  parseDate( date, c_int  )
    r = nil
    date.upcase.each_char do |c| 
        if ["X", "U", "?"].include?(c) 
            r = ( (r.nil?) ? c_int  : r + c_int  )
        else
            r = ( (r.nil?) ? c : r + c )
        end
    end
    return r
end

def to_jsonfile (jsondata, jsonfile, records_dir)
    file_name = "#{records_dir}/#{jsonfile}_#{Time.now.to_i}_#{rand(1000)}.json"
    File.open(file_name, 'wb') do |f|
      f.puts jsondata.to_json
    end
rescue Exception => e
    raise "unable to save to jsonfile: #{e.message}"
end

def convert_hash_keys(value)
    case value
    when Array
        value.map { |v| convert_hash_keys(v) }
    when Hash
        Hash[value.map { |k, v| [ k.to_s, convert_hash_keys(v)] } ]
    else
        value
    end
end

def create_record(jsondata)

    if jsondata['@context'].is_a? String 
        jsondata['@context'] = [ jsondata['@context'] , "@language": "nl" ]
    end

    fromprocessingtime = Time.now.strftime("%Y-%m-%d %H:%M:%S")
    
    jsondata['processingtime'] = "#{ Time.now.strftime("%Y-%m-%d %H:%M:%S") }"

    jsondata["@context"][0] = { "schema": "schema.org" }
    lang = jsondata["@context"][1]["@language"] || "nl"


    unless jsondata["author"].nil? 
        if jsondata["creator"].nil?
            jsondata["creator"] = jsondata["author"]
            jsondata.delete("author")
        end
    end

    unless  jsondata["citation"].nil?
        jsondata["citation"]["@context"][0] = { "schema": "schema.org" }
        unless  jsondata["citation"]["citation"].nil?
            jsondata["citation"].delete("citation") 
        end 
    end


    unless @es_index == "icandid_twitter_sets_v2"
      checkLangauge( jsondata, 'text', lang )
    end
    checkLangauge( jsondata, 'name', lang )
    checkLangauge( jsondata, 'keywords', lang )

    if jsondata['datePublished'].is_a?(Array)
        datePublished =  jsondata['datePublished'][0]
    else
        datePublished = jsondata['datePublished'] 
    end 

    unless datePublished.nil?
        if (datePublished =~ /^.*([0-9UX?]{4}-[0-9UX?]{4}).*$/)
            datePublished = datePublished[/^.*([0-9UX?]{4}-[0-9UX?]{4}).*$/,1]
        end

        if (datePublished =~ /^[0-9UX?]{4}-[0-9UX?]{4}$/) 
            fromyear = parseDate( datePublished[0, 4],"0");
            tillyear = parseDate( datePublished[5, 9],"9");
        end

        if (datePublished =~ /^[0-9UX?]{4}$/) 
            fromyear = parseDate( datePublished[0, 4],"0");
            tillyear = parseDate( datePublished[0, 4],"9");
        end

        if !fromyear.nil? && !tillyear.nil?
            datePublished_time_frame = ["gte": "0000"];
            datePublished_time_frame = {
                "gte" => fromyear ,
                "lte" => tillyear  
            }
        end
        datePublished_time_frame = {
            "gte" => datePublished ,
            "lte" => datePublished  
        }

        jsondata['datePublished_time_frame'] = datePublished_time_frame

        jsondata['datePublished'] = "#{ (DateTime.parse( datePublished )).strftime("%Y-%m-%d") }"
    end

    if jsondata['dateCreated'].is_a?(Array)
        dateCreated =  jsondata['dateCreated'][0]
    else
        dateCreated = jsondata['dateCreated'] 
    end 

    unless dateCreated.nil?
        if (dateCreated =~ /^.*([0-9UX?]{4}-[0-9UX?]{4}).*$/)
            dateCreated = dateCreated[/^.*([0-9UX?]{4}-[0-9UX?]{4}).*$/,1]
        end

        if (dateCreated =~ /^[0-9UX?]{4}-[0-9UX?]{4}$/) 
            fromyear = parseDate( dateCreated[0, 4],"0");
            tillyear = parseDate( dateCreated[5, 9],"9");
        end

        if (dateCreated =~ /^[0-9UX?]{4}$/) 
            fromyear = parseDate( dateCreated[0, 4],"0");
            tillyear = parseDate( dateCreated[0, 4],"9");
        end

        if !fromyear.nil? && !tillyear.nil?
            dateCreated_time_frame = ["gte": "0000"];
            dateCreated_time_frame = {
                "gte" => fromyear ,
                "lte" => tillyear  
            }
        end
        jsondata['dateCreated_time_frame'] = dateCreated_time_frame
    end

    tillprocessingtime = Time.now.strftime("%Y-%m-%d %H:%M:%S")
    processingtime_time_frame = {
        "gte" => fromprocessingtime ,
        "lte" => tillprocessingtime 
    }
    jsondata['processingtime_time_frame'] = processingtime_time_frame

    jsondata.reject!{ |j| j.empty? || j.nil? }
    jsondata.reject!{ |k,v| v.nil? || v.to_s.empty? }

    return jsondata

end

def preprocess_records(files_to_process)
    data_array = []
    files_to_process.each do |jsonfile|
        data_array << JSON.parse( File.read("#{jsonfile}") )
    end
    merge_json(data_array)
end

def merge_json(data_array)
    data = data_array.inject do |outputdata, data| 
        if (outputdata.is_a?(Hash) && data.is_a?(Hash) && outputdata["@id"] != data["@id"] )
          outputdata = [data, outputdata]
        elsif (outputdata.is_a?(Array) && data.is_a?(Hash) && !outputdata.map{ |k| k["@id"] }.include?(data["@id"]) )
          outputdata << data
        elsif (outputdata.is_a?(Array) && data.is_a?(Hash) && outputdata.map{ |k| k["@id"] }.include?(data["@id"]) )
            outputdata.map do |d|
              if d["@id"] == data["@id"]
                merge_json([d,data])
              else
                d
              end
            end
        else
          outputdata.merge!(data) do |key, v1, v2|
            #puts "inspect v1 #{v1.inspect}"
            #puts "inspect v2 #{v2.inspect}"
            if v1 == v2
              # puts "equal #{key}"
              v1
            else
              if ONLY_ONE_VALUE_ALLOWED.include?(key)
                puts "inspect key #{key.inspect}"
                v2
              elsif v1.is_a?(Array)
                if v2.is_a?(Array)
                  (v1 + v2).uniq
                elsif v2.is_a?(Hash)
                  merge_json([v1,v2])
                elsif v2.is_a?(String)
                    v1 << v2
                    v1.uniq
                else
                  raise "Can't merge a #{v2.inspect} to a hash!"
                end
              elsif v1.is_a?(Hash)
                if v2.is_a?(Array)
                  if v2.all? { |h| h.is_a?(Hash) }
                    v2 << v1
                    v2.uniq
                  else
                    raise "Can't merge a #{v2.inspect} to a hash!"
                  end
                elsif v2.is_a?(Hash)
                    merge_json([v1,v2])
                else
                  raise "Can't merge a #{v2.inspect} to a hash!"
                end
              else
                if v2.is_a?(String) || v2.is_a?(Integer)
                  [v1,v2].uniq
                elsif v2.is_a?(Array)
                  v2 << v1
                  v2.uniq
                elsif v2.is_a?(Hash)
                  if v2.has_key?("@value")  && v2.has_key?("@language")
                    raise "COMP v1 #{v1} v2@value #{v2["@value"]} )"
                  else
                    raise "Can't merge a #{v2.inspect} to a hash! (v1 string? class #{v1.class} )"
                  end
                else
                  
                  puts "---------------------------"
                  puts "v1"
                  pp v1
                  puts "---------------------------"
                  puts "v2"
                  pp v2
                  raise "Can't merge a #{v2.inspect} to a hash!"
                end
              end
            end
        end
      end
      outputdata
    end
    convert_hash_keys(data)
end