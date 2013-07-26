Tweetledee
==========

**A PHP library that provides an incredibly easy way to access Twitter data as JSON, pretty printed JSON, or RSS feeds by URL or standard command line syntax.**

## Documentation
<a href="http://chrissimpkins.github.io/tweetledee
">http://chrissimpkins.github.io/tweetledee</a>

## Current Release
 - <b>0.3.0</b> : You can now access Tweetledee from the command line locally or remotely via SSH and pipe the output to any application.  Data is returned via the standard output stream when you access files with a terminal.  Tweetledee will parse the parameters as standard command line switches.  For single character parameters use short switches <code>-q</code> and for multiple character parameters use long switches <code>--user</code>.
 - This feature requires PHP 5.3 or higher

## Recent Changes
 - <b>0.2.9</b> : Added Twitter user lists RSS feeds <code>listsrss.php</code>, JSON <code>listsjson.php</code>, pretty printed JSON <code>listsjson_pp.php</code>

## In the Pipeline
 - Add a caching mechanism to prevent users from exceeding the Twitter API rate limits
 - Add options for cross site request access control headers to all files (access Twitter data with client side JS)
 - Add user mentions data type (JSON and RSS feeds)
 - Add a file updater

## The 1.5 Minute Guide to a Successful Install
**You will need the following**:
 - Access via URL: PHP version 5.3 or higher (5.4 or higher for pretty printed JSON)
 - Access via command line: PHP version 5.3 or higher (5.4 or higher for pretty printed JSON)
 - libcurl installed (provides cURL - http://curl.haxx.se/libcurl/)
 - A <a href="https://dev.twitter.com/apps/new">Twitter application account</a> from which you will obtain the:

	1) consumer key
	2) consumer secret
	3) access key
	4) access secret

### 3-Step Installation instructions:

1. Open the file on the path tweetledee > tldlib > keys > tweetledee_keys.php in any text editor and enter the information that you obtained from your Twitter app in the corresponding fields.  Leave the single quotes around the alphanumeric strings that you enter.

2. Upload the 'tweetledee' directory (that is located in the directory where this README file resides) to the public facing directory on your web server.  On many servers, this is the public_html directory

3. Kick the tires with the following test (it gives you a user timeline RSS feed for your account):
	http://[yourdomain]/tweetledee/userrss.php

That was easy... Go crazy, be good, have fun.

## What You Get
#### Twitter Favorites RSS Feed [<code>favoritesrss.php</code>]
#### Twitter Favorites JSON [<code>favoritesjson.php</code>]
#### Twitter Favorites Pretty Printed JSON [<code>favoritesjson_pp.php</code>]
#### Twitter Home Timeline RSS Feed [<code>homerss.php</code>]
#### Twitter Home Timeline JSON [<code>homejson.php</code>]
#### Twitter Home Timeline Pretty Printed JSON [<code>homejson_pp.php</code>]
#### Twitter User Lists RSS Feed [<code>listsrss.php</code>]
#### Twitter User Lists JSON [<code>listsjson.php</code>]
#### Twitter User Lists Pretty Printed JSON [<code>listsjson_pp.php</code>]
#### Twitter User Timeline RSS Feed [<code>userrss.php</code>]
#### Twitter User Timeline JSON [<code>userjson.php</code>]
#### Twitter User Timeline Pretty Printed JSON [<code>userjson_pp.php</code>]
#### Twitter Search RSS Feed [<code>searchrss.php</code>]
#### Twitter Search JSON [<code>searchjson.php</code>]
#### Twitter Search Pretty Printed JSON [<code>searchjson_pp.php</code>]

## Usage
<a href="http://chrissimpkins.github.io/tweetledee/usage.html">Tweetledee Usage Examples</a>

## Bugs & Questions
If you find a bug, please post it as a new issue on the GitHub repository with <a href="http://chrissimpkins.github.io/tweetledee/support.html#bug-reporting">this information in your report</a>.

Looking for support? Check <a href="http://chrissimpkins.github.io/tweetledee/support.html">this page</a>.

If you would like to contribute to the project, by all means, please do so.  Fork Tweetledee and submit a pull request back to the repository.  I will make a development branch if there is any significant interest in contributing to the project.

## License
MIT License - see the LICENSE.txt file in the source distribution

✪ Chris

