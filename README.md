Tweetledee
==========

**A PHP library that provides an incredibly easy way to access Twitter data as JSON, pretty printed JSON, or RSS feeds by URL or standard command line syntax.  The Tweetledee files include caching to avoid exceeding the Twitter API v1.1 rate limits (see [caveats in the documentation](http://tweetledee.github.io/tweetledee/caching.html)!).**

## Documentation
- Docs Home: [http://tweetledee.github.io/tweetledee](http://tweetledee.github.io/tweetledee)
- Usage: [http://tweetledee.github.io/tweetledee/usage.html](http://tweetledee.github.io/tweetledee/usage.html)
- Developer Docs: [http://tweetledee.github.io/tweetledee/developer.html](http://tweetledee.github.io/tweetledee/developer.html)

## Current Release
- <b>0.5.2</b> : Quoted tweets can be displayed with a rl=1 query parameter in the URL. All images for the tweet are loaded now.

## Changes
- <b>0.5.1</b> : Improved hastag display.
- <b>0.5.0</b> : First release as the tweetledee organization. Thanks to Christopher Simpkins for encouraging other people to continue with the project. Now the full tweet is displayed following the 280 characters from twitter. Media is loaded as https. Added Some reference dockerfiles.
- <b>0.4.2</b> : added support for inline images in all RSS scripts [issue #51](https://github.com/tweetledee/tweetledee/issues/51).  A big thanks to Vinh Nguyen for his pull request to add support for this feature!
- <b>0.4.1</b> : added support for JSON pretty printing in PHP versions 5.3+ (from 5.4+ previously) [issue #40](https://github.com/tweetledee/tweetledee/pull/40).  Thanks much to Martín Lucas Golini @SpartanJ for his new pretty printing functions.
- <b>0.4.0</b> : added caching to all Tweetledee files with default 90 sec duration.  This default cache interval can be changed with the `cache_interval` URL parameter (with a value in seconds).  Great big thanks to Christian Varga (@levymetal) for his contributions to this update!  The non-cached versions of the files from v0.3.6 have been renamed with an appended `_nocache` (e.g. `userrss.php` > `userrss_nocache.php`) for anyone who would like to implement their own caching.
- <b>0.3.6</b> : bug fix for multi-parameter search query exception bug [issue #30](https://github.com/tweetledee/tweetledee/issues/30).  Thanks much for the issue report @adjeD!
- <b>0.3.5</b> : bug fix for Twitter search filters [issue #28](https://github.com/tweetledee/tweetledee/issues/28).  Thanks much for the issue report @molis83!
- <b>0.3.4</b> : added Python and Ruby wrappers for the Tweetledee files
- <b>0.3.3</b> : bug fixes for [issue #15](https://github.com/tweetledee/tweetledee/issues/15) & [issue #16](https://github.com/tweetledee/tweetledee/issues/16).  Thanks much for the contributions from @jjschwartz, @kabookey, and @mikeklimczak.
- <b>0.3.2</b> : bug fixes for [issue #14](https://github.com/tweetledee/tweetledee/issues/14)
- <b>0.3.1</b> : Updated all standard JSON files with cross site access to your Twitter JSON data from client side JavaScript code (sets the Access-Control-Allow-Origin header to accept all connections, i.e. cross origin resource sharing). Defaults to off.  Set the flag `$TLD_JS = 1` in the file to activate this capability.
- <b>0.3.0</b> : You can now access Tweetledee from the command line locally or remotely via SSH and pipe the output to any application.  Data is returned via the standard output stream when you access files with a terminal.  Tweetledee will parse the parameters as standard command line switches.  For single character parameters use short switches <code>-q</code> and for multiple character parameters use long switches <code>--user</code>.
- <b>0.2.9</b> : Added Twitter user lists RSS feeds <code>listsrss.php</code>, JSON <code>listsjson.php</code>, pretty printed JSON <code>listsjson_pp.php</code>

## The 1.5 Minute Guide to a Successful Install
**You will need the following**:
 - Access via URL: PHP version 5.3 or higher
 - Access via command line: PHP version 5.3 or higher
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
### Twitter RSS Feeds
##### Favorites RSS Feed [<code>favoritesrss.php</code>] + [<code>favoritesrss_nocache.php</code>]
##### Home Timeline RSS Feed [<code>homerss.php</code>] + [<code>homerss_nocache.php</code>]
##### User Lists RSS Feed [<code>listsrss.php</code>] + [<code>listrss_nocache.php</code>]
##### User Timeline RSS Feed [<code>userrss.php</code>] + [<code>userrss_nocache.php</code>]
##### Search RSS Feed [<code>searchrss.php</code>] + [<code>searchrss_nocache.php</code>]

### Twitter JSON
##### Favorites JSON [<code>favoritesjson.php</code>] + [<code>favoritesjson_nocache.php</code>]
##### Home Timeline JSON [<code>homejson.php</code>] + [<code>homejson_nocache.php</code>]
##### User Lists JSON [<code>listsjson.php</code>] + [<code>listsjson_nocache.php</code>]
##### User Timeline JSON [<code>userjson.php</code>] + [<code>userjson_nocache.php</code>]
##### Search JSON [<code>searchjson.php</code>] + + [<code>searchjson_nocache.php</code>]

### Pretty Printed JSON
##### Favorites Pretty Printed JSON [<code>favoritesjson_pp.php</code>] + [<code>favoritesjson_pp_nocache.php</code>]
##### Home Timeline Pretty Printed JSON [<code>homejson_pp.php</code>] + [<code>homejson_pp_nocache.php</code>]
##### User Lists Pretty Printed JSON [<code>listsjson_pp.php</code>] + [<code>listsjson_pp_nocache.php</code>]
##### User Timeline Pretty Printed JSON [<code>userjson_pp.php</code>] + [<code>userjson_pp_nocache.php</code>]
##### Search Pretty Printed JSON [<code>searchjson_pp.php</code>] + [<code>searchjson_pp_nocache.php</code>]

## Usage
<a href="http://tweetledee.github.io/tweetledee/usage.html">Tweetledee Usage Examples</a>

## Bugs & Questions
If you find a bug, please post it as a new issue on the GitHub repository with <a href="http://tweetledee.github.io/tweetledee/support.html#bug-reporting">this information in your report</a>.

Looking for support? Check <a href="http://tweetledee.github.io/tweetledee/support.html">this page</a>.


## Contribute
If you would like to contribute to the project, have at it.  <a href="https://help.github.com/articles/fork-a-repo">Fork the Tweetledee project</a>, include your changes, and <a href="https://help.github.com/articles/using-pull-requests">submit a pull request</a> back to the main repository.

## License
MIT License - see the LICENSE.txt file in the source distribution
