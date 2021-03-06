<?php

/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   searchrss.php -- Tweet search query results formatted as RSS feed
 * Copyright 2014 Christopher Simpkins
 * MIT License
 ************************************************************************************************/

/*-----------------------------------------------------------------------------------------------
==> Instructions:
    - place the tweetledee directory in the public facing directory on your web server (frequently public_html)
    - Generic tweet search RSS feed URL (count = 25):
            e.g. http://<yourdomain>/tweetledee/searchrss.php?q=<search-term>
==> Twitter Tweet Search RSS feed parameters:
    - 'c'   - specify a tweet count (range 1 - 200, default = 25)
            e.g. http://<yourdomain>/tweetledee/searchrss.php?q=<search-term>&c=100
    - 'rt'  - result type (possible values: mixed, recent, popular; default = mixed)
            e.g. http://<yourdomain>/tweetledee/searchrss.php?q=<search-term>&rt=recent
    - 'q'   - query term
             e.g. http://<yourdomain>/tweetledee/searchrss.php?q=coolsearch
    - 'cache_interval' - specify the duration of the cache interval in seconds (default = 90sec)
    - 'recursion_limit' - When a tweet is a reply, specifies the maximum number of "parents" tweets to load (default = 0).
                        A value of 10 can be used without significative performance cost on Raspberry 3.
                        This can be short-handed to 'rl'
--------------------------------------------------------------------------------------------------*/

/*******************************************************************
 *  Includes
 ********************************************************************/
require 'tldlib/debug.php';
// Matt Harris' Twitter OAuth library
require 'tldlib/tmhOAuth.php';
require 'tldlib/tmhUtilities.php';

// include user keys
require 'tldlib/keys/tweetledee_keys.php';

// include Geoff Smith's utility functions
require 'tldlib/tldUtilities.php';

// include Christian Varga's twitter cache
require 'tldlib/tldCache.php';

require 'tldlib/renderers/rss.php';

require 'tldlib/parametersProcessing.php';

$parameters = load_parameters([
    "c",
    "query",
    "rt",
    "cache_interval",
    "recursion_limit"
]);
extract($parameters);
if (!isset($query)) {
    die("Error: missing the search query term.  Please use the 'q' parameter.");
}

/*******************************************************************
 *  OAuth
 ********************************************************************/
$tldCache = new tldCache([
    'consumer_key'        => $my_consumer_key,
    'consumer_secret'     => $my_consumer_secret,
    'user_token'          => $my_access_token,
    'user_secret'         => $my_access_token_secret,
    'curl_ssl_verifypeer' => false
], $cache_interval);

// request the user information
$data = $tldCache->auth_request();

// Parse information from response
$twitterName = $data['screen_name'];
$fullName = $data['name'];
$twitterAvatarUrl = $data['profile_image_url_https'];

//Create the feed title with the query
$feedTitle = 'Twitter search for "' . $query . '"';

// URL encode the search query
//$urlquery = urlencode($query);

/*******************************************************************
 *  Request
 ********************************************************************/
$searchResultsObj = $tldCache->user_request([
    'url' => '1.1/search/tweets',
    'params' => [
        'include_entities' => true,
        'count' => $count,
        'result_type' => $result_type,
        'q' => $query,
    ]
]);

//concatenate the URL for the atom href link
if (defined('STDIN')) {
    $thequery = $_SERVER['PHP_SELF'];
} else {
    $thequery = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
}

// Start the output
header("Content-Type: application/rss+xml");
header("Content-type: text/xml; charset=utf-8");

$renderer = new RssRenderer($recursion_limit);
$renderer->using_cache($tldCache);
$config = [
    'atom'              =>  $my_domain . urlencode($thequery),
    'link'               =>  sprintf('http://www.twitter.com/search/?q=%s', $query),
    'lastBuildDate'     =>  date(DATE_RSS),
    'title'             =>  $feedTitle,
    'description'       =>  sprintf(
        'A Twitter search for the query "%s" with the %s search result type',
        $query,
        $result_type
    ),
    'twitterAvatarUrl'  =>  $twitterAvatarUrl,
];
echo $renderer->render_feed($config, $searchResultsObj['statuses']);
