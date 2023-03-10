
lib = File.expand_path("../lib", __FILE__)
$LOAD_PATH.unshift(lib) unless $LOAD_PATH.include?(lib)

Gem::Specification.new do |spec|
  spec.name          = "data_collector"
  spec.version       = "0.0.1"
  spec.authors       = ["MC","TV"]
  spec.summary       = %q{ETL helper library}
  spec.description   = %q{INPUT, FILTER, OUTPUT data with RULES and code}
  spec.license       = "MIT"

  # Prevent pushing this gem to RubyGems.org. To allow pushes either set the 'allowed_push_host'
  # to allow pushing to a single host or delete this section to allow pushing to any host.
  if spec.respond_to?(:metadata)

  else
    raise "RubyGems 2.0 or newer is required to protect against " \
      "public gem pushes."
  end

  # Specify which files should be added to the gem when it is released.
  # The `git ls-files -z` loads the files in the RubyGem that have been added into git.
  spec.files         = Dir.chdir(File.expand_path('..', __FILE__)) do
    #`git ls-files -z`.split("\x0").reject { |f| f.match(%r{^(test|spec|features)/}) }
    Dir.glob("{lib,exe}/**/*", File::FNM_DOTMATCH).reject {|f| File.directory?(f) }
  end
  spec.bindir        = "exe"
  spec.executables   = spec.files.grep(%r{^exe/}) { |f| File.basename(f) }
  spec.require_paths = ["lib"]

  spec.add_runtime_dependency "nokogiri", "~> 1.10"
  spec.add_runtime_dependency "json", "~> 2.2"
  spec.add_runtime_dependency "jsonpath", "~> 1.0"
  spec.add_runtime_dependency "nori", "~> 2.6"
  spec.add_runtime_dependency "http", "~> 4.1"
  spec.add_runtime_dependency "mime-types", "~> 3.2"
  spec.add_runtime_dependency "minitar", "= 0.9"
  spec.add_runtime_dependency "activesupport", "~> 5.2"
  spec.add_runtime_dependency "json-ld", "~> 3.1"

  spec.add_development_dependency "bundler", "~> 2.0"
  spec.add_development_dependency "rake", "~> 12.3"
  spec.add_development_dependency "minitest", "~> 5.0"
  spec.add_development_dependency "webmock", "~> 3.10"

end
