#!/usr/bin/env python

#################################################################
# Tweetledee.py
# A Python programming language wrapper for the Tweetldee project
# Copyright 2013 Christopher Simpkins
# MIT License
#################################################################

import sys
import os
import subprocess

def main(argv):
	tld_path = "../tweetledee/userjson.php" 	# default Tweetledee file
	the_command = ""
	arguments = " ".join(argv)

	if argv:
		if argv[0].endswith(".php"):
			# user requested file other than the Tweetledee default file (tld_path)
			tld_path = argv[0]
			arguments = " ".join(argv[1:]) #remove the filepath in the argument list
		the_command = "php " + tld_path + " " + arguments
		tldrun(the_command)
	else:
		# there were no arguments passed at the CL, run the file without arguments
		the_command = "php " + tld_path
		tldrun(the_command)

def tldrun(cmd):
	try:
		result = subprocess.check_output(cmd, stderr=subprocess.STDOUT, shell=True)
		print(result)
	except Exception as e:
		print("There was an error running the Tweetledee file")
		print((str(e)))
		sys.exit(1)

if __name__ == '__main__':
	main(sys.argv[1:])
