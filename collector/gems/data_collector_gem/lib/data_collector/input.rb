#encoding: UTF-8
require 'http'
require "net/https" 
require 'open-uri'
require 'nokogiri'
require 'json/ld'
require 'nori'
require 'uri'
require 'logger'
require 'cgi'
require 'mime/types'
require 'active_support/core_ext/hash'
require 'zlib'
require 'minitar'
require 'csv'
require 'zip'

#require_relative 'ext/xml_utility_node'
module DataCollector
  class Input
    attr_reader :raw

    def initialize
      @logger = Logger.new(STDOUT)
      @logger.level = Logger::INFO
    end

    def from_uri(source, options = {})
      source = CGI.unescapeHTML(source)
      @logger.info("Loading #{source}")
      uri = URI(source)
      begin
        data = nil
        case uri.scheme
        when 'http'
          data = from_http(uri, options)
        when 'https'
          data = from_https(uri, options)
        when 'file'
          data = from_file(uri, options)
        else
          raise "Do not know how to process #{source}"
        end

        data = data.nil? ? 'no data found' : data

        if block_given?
          yield data
        else
          data
        end
      rescue => e
        @logger.warn(e.message)
        @logger.warn("DATA")
        @logger.warn(data)
        puts e.backtrace.join("\n")
        nil
      end
    end

    private

    def from_http(uri, options = {})
      from_https(uri, options)
    end

    def from_https(uri, options = {})
      data = nil
      user = options[:user] || nil
      password = options[:password] || nil
      bearer_token = options[:bearer_token] || nil
      headers =  options[:headers] || nil


      http = Net::HTTP.new(uri.host, uri.port)
      http.use_ssl = true
      http.verify_mode = OpenSSL::SSL::VERIFY_NONE

      request = Net::HTTP::Get.new(uri.request_uri)
  
      unless user.nil? and password.nil?
        @logger.debug "Set Basic_auth"
        request.basic_auth user, password
        #  http_response = HTTP.basic_auth(user: user, pass: password).get(escape_uri(uri))
      end
      unless bearer_token.nil?
        @logger.debug  "Set authorization bearer token"
        request["Authorization"] = "Bearer #{bearer_token}"
      end
      unless headers.nil? || headers.empty?
        @logger.debug  "Set headers"
        headers.each do |key, value|
          request[key] = value
        end
      end

      http_response = http.request(request)

      case http_response.code.to_i
      when 200
        @raw = data = http_response.body.to_s

        # File.open("#{rand(1000)}.xml", 'wb') do |f|
        #   f.puts data
        # end

        file_type = options.with_indifferent_access.has_key?(:content_type) ? options.with_indifferent_access[:content_type] : file_type_from(http_response.each_header)

        unless options.with_indifferent_access.has_key?(:raw) && options.with_indifferent_access[:raw] == true
          case file_type
          when 'application/ld+json'
            data = JSON.parse(data)
          when 'application/json'
            data = JSON.parse(data)
          when 'application/atom+xml'
            data = xml_to_hash(data)
          when 'text/csv'
            data = csv_to_hash(data)
          when 'application/xml'
            data = xml_to_hash(data)
          when 'text/xml'
            data = xml_to_hash(data)
          else
            data = xml_to_hash(data)
          end
        end
      when 400
        pp http_response
        unless http_response.body.nil?
          @raw = data = http_response.body.to_s
        end
        data = JSON.parse %({"error": {"code": "#{http_response.code}", "message": "#{http_response.message}"}})  
        @logger.warn("#{http_response.code} : #{http_response.message}")        
      when 401
        unless http_response.body.nil?
          @raw = data = http_response.body.to_s
        end
        data = JSON.parse %({"error": {"code": "#{http_response.code}", "message": "#{http_response.message}"}})  
        @logger.warn("#{http_response.code} : #{http_response.message}")  
#        raise 'Unauthorized'
      when 404
        raise 'Not found'
      when 429
        unless http_response.body.nil?
          @raw = data = http_response.body.to_s
        end
        data = JSON.parse %({"error": {"code": "#{http_response.code}", "message": "#{http_response.message}"}})  
        @logger.warn("#{http_response.code} : #{http_response.message}")  
      else
        puts http_response
        raise "Unable to process received status code = #{http_response.code}"
      end
      data
    end

    def from_file(uri, options = {})
      data = nil
      absolute_path = File.absolute_path("#{uri.host}#{uri.path}")
      unless options.has_key?('raw') && options['raw'] == true
        @raw = data = File.read("#{absolute_path}")
        case File.extname(absolute_path)
        when '.jsonld'
          data = JSON.parse(data)
        when '.json'
          data = JSON.parse(data)
        when '.xml'
          data = xml_to_hash(data)
        when '.gz'
          Minitar.open(Zlib::GzipReader.new(File.open("#{absolute_path}", 'rb'))) do |i|
            i.each do |entry|
              data = entry.read
            end
          end
          data = xml_to_hash(data)
        when '.csv'
          data = csv_to_hash(data)
        else
          raise "Do not know how to process #{uri.to_s}"
        end
      end
      data
    end

    def xml_to_hash(data)
      #gsub('&lt;\/', '&lt; /') outherwise wrong XML-parsing (see records lirias1729192 )
      data = data.gsub /&lt;/, '&lt; /'
      nori = Nori.new(parser: :nokogiri, strip_namespaces: true, convert_tags_to: lambda { |tag| tag.gsub(/^@/, '_') })
      nori.parse(data)
      #JSON.parse(nori.parse(data).to_json)
    end

    def csv_to_hash(data)
      csv = CSV.parse(data, headers: true, header_converters: [:downcase, :symbol])

      csv.collect do |record|
        record.to_hash
      end
    end

    def escape_uri(uri)
      #"#{uri.to_s.gsub(uri.query, '')}#{CGI.escape(CGI.unescape(uri.query))}"
      uri.to_s
    end

    def file_type_from(headers)
      headers = headers.map {|k,v| [(k.respond_to?(:downcase) ? k.downcase : k), v] }.to_h
      file_type = 'application/octet-stream'
      file_type = if headers.key?('content-type')
                    headers['content-type'].split(';').first
                  else
                    @logger.debug  "No Header content-type available"
                    MIME::Types.of(filename_from(headers)).first.content_type
                  end
      return file_type
    end

    
    def unzip_file (file, destination)
      @logger.info("Unzip #{file}") 
      Zip::ZipFile.open(file) do |zip_file|
        
        @logger.info("Unzip zip_file #{zip_file}") 
  
          zip_file.each do |f|
              f_path = File.join(destination, f.name)
              FileUtils.mkdir_p(File.dirname(f_path))
              f.extract(f_path) unless File.exist?(f_path)
          end
      end
    end

  end
end
