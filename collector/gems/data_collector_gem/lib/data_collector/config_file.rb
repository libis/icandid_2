#encoding: UTF-8

require 'yaml'

module DataCollector
  class ConfigFile
    @config = {}
    @config_file = 'config.yml'
    @config_file_path = ''

    def self.version
      '0.0.1'
    end

    def self.path
      @config_file_path
    end

    def self.path=(config_file_path)
      @config_file_path = config_file_path
    end

    def self.file
      @config_file
    end

    def self.file=(config_file)
      @config_file = config_file
    end

    def self.[](key)
      init
      @config[key]
    end

    def self.[]=(key, value)
      init
      # puts @config[key] 

      @config[key] = value
      File.open("#{path}/#{file}", 'w') do |f|
        # puts "read file"
        # puts f
        f.puts @config.to_yaml
      end
    end

    def self.include?(key)
      init
      @config.include?(key)
    end

    private_class_method def self.init
      discover_config_file_path
      if @config.empty?
        config = YAML::load_file("#{path}/#{file}")
        @config = process(config)
      end
    end


    private_class_method def self.discover_config_file_path
      if @config_file_path.nil? || @config_file_path.empty?
        if File.exist?(file)
          @config_file_path = '.'
        elsif File.exist?("config/#{file}")
          @config_file_path = 'config'
        end
      end
    end

    private_class_method def self.process(config)
      new_config = {}
      config.each do |k, v|
        if config[k].is_a?(Hash)
          v = process(v)
        end
        new_config.store(k.to_sym, v)
      end
      new_config
    end
  end
end
