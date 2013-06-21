tweetledee
==========

**A PHP library that provides an incredibly easy way to access Twitter data as pretty printed JSON or XML RSS feed**

## The 1.5 Minute Guide to Successful Installation
**You will need the following**:
 - Access to a web server with PHP version 5.1.2 or higher
 - libcurl installed on your web server (provides cURL - http://curl.haxx.se/libcurl/)
 - A Twitter "app" from which you will obtain the:
	1) consumer key
	2) consumer secret
	3) access key
	4) access secret

### 3-Step Installation instructions:

1. Open the file on the path tweetledee > tldlib > keys > tweetledee_keys.php in any text editor and enter the information that you obtained from your Twitter app in the corresponding fields.  Leave the single quotes around the alphanumeric strings that you enter.

2. Upload the 'tweetledee' directory (that is located in the directory where this README file resides) to the public facing directory on your web server.  On many servers, this is the public_html directory

3. Kick the tires with the following test (it gives you a syndicated feed of the user timeline for my @0labs account):
	http://<yourdomain>/tweetledee/userrss.php?user=0labs

That was easy... Go crazy, be good, have fun.

## Usage
Documentation in progress...stay tuned.  Source files fully documented with defaults and available parameters to modify defaults in the meantime
#### Twitter User Home Timeline RSS (homerss.php)
#### Twitter User Home Timeline JSON (homejson.php)
#### Twitter User Timeline RSS (userrss.php)
#### Twitter User Timeline JSON (userjson.php)
#### Twitter Search RSS (searchrss.php)
#### Twitter Search JSON (searchjson.php)

## Bugs & Questions
If you find a bug, please post it as a new issue on the GitHub repository.

If you have questions or comments, feel free to drop me a line @0labs on Twitter.

If you would like to contribute to the project, by all means, please do so.

## License
MIT License - see the LICENSE.txt file in the source distribution

~Chris
@0labs

