#!/usr/bin/env ruby
# coding: utf-8

###############################################################
# Tweetledee.rb
# A Ruby programming language wrapper for the Tweetledee project
# Copyright 2013 Christopher Simpkins
# MIT License
###############################################################

require 'open3'

# Default Tweetledee file path
tld_path='../tweetledee/userjson.php'

# Include optional Tweetledee file path as first argument to modify default
if ARGV.count > 0
	if ARGV[0].include? ".php"
		tld_path = ARGV[0]
		ARGV.shift
	end
end
argstring = ARGV.join(" ")
comstring = "php #{tld_path} " + argstring
stdout_str, stderr_str, status = Open3.capture3(comstring)
unless status.exitstatus == 0
	STDERR.puts "Unable to process the Tweetledee file"
	STDERR.puts stderr_str
	exit 1
end
puts stdout_str

