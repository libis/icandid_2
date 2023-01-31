require "iso639"
require "unicode"
require "unicode/scripts"

module Utils

  def schema_out_object(id:, type:, url: "#{@url_prefix}/#{id}")
    begin
      {
        :@context => ["http://schema.org", {
          :@language => "#{@metaLanguage}-#{@unicode_script}"
        }],

        :@id => "#{ id }",
        :@type => "#{ type }",
        :additionalType => "CreativeWork",

        # Will be set loading AgensGraph
        # :sdPublisher => @sdPublisher,
        # :sdDatePublished => Time.now.strftime("%Y-%m-%d"),
        # :sdLicense => @sdLicense,
        # :url => "#{url}",
        # :thumbnailUrl => [],

        :isBasedOn => @isBasedOn
    }
    rescue
      puts "Error init_schema_out_object: #{msg}"
    end
  end

  def ingestConf(ingestConf, conf)
    @dataset = ingestConf[:dataset]
    @provider = ingestConf[:provider]
    @license = ingestConf[:license]

    @mediaUrlPrefix = ingestConf[:mediaUrlPrefix]
    @same_as_template = ingestConf[:same_as_template]

    @metaLanguage = ingestConf[:metaLanguage]
    @unicode_script = ingestConf[:unicode_script]
    @recordLanguage = ingestConf[:recordLanguage]
    @defaulttype =ingestConf[:defaulttype]   
    @genericRecordDesc = ingestConf[:genericRecordDesc]

    ingestdata = {
      :provider => @provider,
      :dataset => @dataset,
      :license => @license,
      :name => @genericRecordDesc,
      :prefix => conf[:prefixid]
   }
  
   @logger.debug("ingestdata #{ ingestdata } ")
  
    @isBasedOn = build_isBasedOn(ingestdata)
  
    @logger.debug("isBasedOn #{ @isBasedOn } ")
  end

  def build_isBasedOn(ingestdata) 
    {
      :@type => "CreativeWork",
      :@id => "#{ingestdata[:prefix]}_#{ ingestdata[:provider][:@id] }_#{ ingestdata[:dataset][:@id]}",
      :license => ingestdata[:license],
      :name => ingestdata[:name],
      :provider => ingestdata[:provider],
      :isPartOf => [
        ingestdata[:dataset]
      ]
    }
  end

  def build_pagination(page_start, page_end)
    unless page_start.nil? || page_end.nil?
        "#{page_start}-#{page_end}"
    end
end

  def script_checker(value, unicode_script)
    begin
        if !value.nil?
            unicode_script = Unicode::Scripts.scripts("#{value}", format: :short)
            {:@value => "#{value}", :@language => "#{output[:language]}-#{unicode_script[0]}"}
        end
    rescue StandardError => msg  
        # display the system generated error message  
        puts "Error script_checker: #{msg}"
        puts "#{value}"
        puts "#{Unicode::Scripts.scripts("#{value}") }"
    end
  end

  def convert_to_place (data, id)
    if data.is_a? String  
      data = [data]
    end
    if data.is_a? Array
      data.each_with_index.map do |l, i|  
        {
            :@type => "Place",
            # id will be added with write_schema_out => add_all_ids
            # otherwise duplicate ids could be generated
            # :@id => "#{id}_PLACE_#{i}", 
            :name => l,
        }
      end
    end
  end

  def write_schema_out(schema_out, id, check_updates)
    begin
      schema_out.reject!{ |k,v| v.nil? || ( (!v.is_a? Integer) && v.empty? ) }

      add_all_ids( data: schema_out, prefix:  "#{id}")

      schema_out = becompact(schema_out)
      
      unless schema_out.empty?
          @logger.debug(" --- records_dir #{@records_dir} ")
          @logger.debug(" --- write #{id}")
          unless schema_out[:name]
              @logger.warn(" schema_out[:name] is missing ! #{schema_out[:name]} ")
              raise "No name in #{id}"
          end

          # If there is already a file with this name write to new data to <filename>_new.json
          # If the data of the 'old' file is NOT the same as the new data
          # move the old fiel to backup
          # override the old file with the new file

          filename = id.gsub(/[.\/\\:\?\*|"<>]/, '-')

          if check_updates
            old_filename = nil
            if File.file?( File.join(@records_dir,"#{filename}.json") )
              @logger.debug(" --- File exists")
              old_filename = filename
              filename = "#{filename}_new"
            end
          end

          output.to_jsonfile(schema_out.compact, filename , @records_dir)
          
          if check_updates
            @logger.debug(" old_filename #{old_filename}" )
            unless old_filename.nil?
              @logger.debug(" compaire #{filename}.json and #{old_filename}.json" )
              old_file = File.join(@records_dir,"#{old_filename}.json")
              new_file = File.join(@records_dir,"#{filename}.json")

              old_json = File.read( old_file )
              new_json = File.read( new_file )
              
              unless JSON.parse(old_json) == JSON.parse(new_json)
                @logger.warn("#{old_file} has an update check backup dir" )
                backup_dir = File.join(@records_dir,"backup")
                backup_file = File.join(backup_dir,"#{old_filename}_#{ Time.now.utc.strftime("%Y%m%d%H%M%S") }.json")
                unless File.directory?(backup_dir)
                  FileUtils.mkdir_p(backup_dir)
                end

                FileUtils.mv(old_file,backup_file)
              end
              FileUtils.mv(new_file, old_file)
            end
          end
      end
      output.clear()

    rescue StandardError => msg  
        # display the system generated error message  
        @logger.error( "Error write_schema_out for record #{id}: #{msg}")
    end
  end
end


