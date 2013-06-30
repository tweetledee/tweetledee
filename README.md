Tweetledee
==========

**A PHP library that provides an incredibly easy way to access Twitter data as JSON, pretty printed JSON, or RSS feeds**

## The 1.5 Minute Guide to Successful Installation
**You will need the following**:
 - Access to a web server with PHP version 5.1.2 or higher (5.4 or higher for pretty printed JSON)
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
	http://[yourdomain]/tweetledee/userrss.php?user=0labs

That was easy... Go crazy, be good, have fun.

## What You Get
#### Twitter Favorites RSS (favoritesrss.php)
#### Twitter Favorites JSON (favoritesjson.php)
#### Twitter Favorites Pretty Printed JSON (favoritesjson_pp.php)
#### Twitter Home Timeline RSS (homerss.php)
#### Twitter Home Timeline JSON (homejson.php)
#### Twitter Home Timeline Pretty Printed JSON (homejson_pp.php)
#### Twitter User Timeline RSS (userrss.php)
#### Twitter User Timeline JSON (userjson.php)
#### Twitter User Timeline Pretty Printed JSON (userjson_pp.php)
#### Twitter Search RSS (searchrss.php)
#### Twitter Search JSON (searchjson.php)
#### Twitter Search Pretty Printed JSON (searchjson_pp.php)

## Usage
<a href="http://chrissimpkins.github.io/tweetledee/usage.html">Tweetledee Usage Examples</a>

## Bugs & Questions
If you find a bug, please post it as a new issue on the GitHub repository.

If you have questions or comments, feel free to drop me a line @pixsied on Twitter.

If you would like to contribute to the project, by all means, please do so.

## License
MIT License - see the LICENSE.txt file in the source distribution

~ Chris

