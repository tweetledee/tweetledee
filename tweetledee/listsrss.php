<?php

/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   listsrss.php -- User list tweets formatted as a RSS feed
 * Copyright 2014 Christopher Simpkins
 * MIT License
 ************************************************************************************************/

/*-----------------------------------------------------------------------------------------------
==> Instructions:
    - place the tweetledee directory in the public facing directory on your web server (frequently public_html)
    - Access the default user list feed (count = 25, includes both RT's & replies) at the following URL:
            e.g. http://<yourdomain>/tweetledee/listsrss.php?list=<list-slug>
==> User List RSS feed parameters:
    - 'c' - specify a tweet count (range 1 - 200, default = 25)
            e.g. http://<yourdomain>/tweetledee/listsrss.php?list=<list-slug>&c=100
    - 'list' - the list name for the specified user (default = account associated with access token)
            e.g. http://<yourdomain>/tweetledee/listsrss.php?list=theblacklist
    - 'user' - specify the Twitter user whose favorites you would like to retrieve (default = account associated with access token)
            e.g. http://<yourdomain>/tweetledee/listsrss.php?list=<list-slug>&user=cooluser
    - 'xrt' - exclude retweets in the returned data (set to 1 to exclude, default = include retweets)
            e.g. http://<yourdomain>/tweetledee/listsrss.php?list=<list-slug>&xrt=1
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
    "user",
    "exclude_retweets",
    "cache_interval",
    "list",
    "recursion_limit"
]);
extract($parameters);
if (!isset($parameters['list'])) {
    die("Error: missing user list name in your request.  Please use the 'list' parameter in your request.");
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
$fullName = $data['name'];
$twitterAvatarUrl = $data['profile_image_url_https'];
if (!isset($screen_name) || $screen_name == '') {
    $screen_name = $data['screen_name'];
}

/*******************************************************************
 *  Request
 ********************************************************************/

$userListObj = $tldCache->user_request([
    'url' => '1.1/lists/statuses',
    'params' => [
        'include_entities' => true,
        'count' => $count,
        'owner_screen_name' => $screen_name,
        'slug' => $list_name,
        'include_rts' => $include_retweets,
    ]
]);

//concatenate the URL for the atom href link
if (defined('STDIN')) {
    $thequery = $_SERVER['PHP_SELF'];
} else {
    $thequery = $_SERVER['PHP_SELF'] . '?' . urlencode($_SERVER['QUERY_STRING']);
}

// Start the output
header("Content-Type: application/rss+xml");
header("Content-type: text/xml; charset=utf-8");

$renderer = new RssRenderer($recursion_limit);
$renderer->using_cache($tldCache);
$config = [
    'atom'              =>  $my_domain . urlencode($thequery),
    'link'               =>  sprintf('http://www.twitter.com/%s/lists/%s', $screen_name, $list_name),
    'lastBuildDate'     =>  date(DATE_RSS),
    'title'             =>  sprintf('Twitter list feed for the %s list %s', $screen_name, $list_name),
    'description'       =>  sprintf('Twitter list feed for the %s list %s', $screen_name, $list_name),
    'twitterAvatarUrl'  =>  $twitterAvatarUrl,
];
echo $renderer->render_feed($config, $userListObj);
