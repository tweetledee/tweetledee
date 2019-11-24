<?php
/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   homerss.php -- Home timeline results formatted as RSS feed
 * Copyright 2014 Christopher Simpkins
 * MIT License
 ************************************************************************************************/
/*-----------------------------------------------------------------------------------------------
==> Instructions:
    - place the tweetledee directory in the public facing directory on your web server (frequently public_html)
    - Access the default home timeline feed (count = 25, includes both RT's & replies) at the following URL:
            e.g. http://<yourdomain>/tweetledee/homerss.php
==> User's Home Timeline RSS feed parameters:
    - 'c' - specify a tweet count (range 1 - 200, default = 25)
            e.g. http://<yourdomain>/tweetledee/homerss.php?c=100
    - 'xrp' - exclude replies (1=true, default = false)
            e.g. http://<yourdomain>/tweetledee/homerss.php?xrp=1
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
    "exclude_replies",
    "cache_interval",
    "recursion_limit"
]);
extract($parameters);
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
$twitterAvatarUrl = $data['profile_image_url'];
$feedTitle = ' Twitter home timeline for ' . $twitterName;

/*******************************************************************
*  Request
********************************************************************/
$homeTimelineObj = $tldCache->user_request([
    'url' => '1.1/statuses/home_timeline',
    'params' => array(
        'include_entities' => true,
        'count' => $count,
        'exclude_replies' => $exclude_replies,
    )
]);

//headers
header("Content-Type: application/rss+xml");
header("Content-type: text/xml; charset=utf-8");

// Start the output

$renderer = new RssRenderer($recursion_limit);
$renderer->using_cache($tldCache);
$config = array(
    'atom'              =>  $my_domain . $_SERVER['PHP_SELF'],
    'link'              =>  sprintf('http://www.twitter.com/%s', $twitterName),
    'twitterName'       => $twitterName,
    'lastBuildDate'     =>  date(DATE_RSS),
    'title'             =>  $feedTitle,
    'description'       =>  sprintf('Twitter home timeline updates for %s/%s', $fullName, $twitterName),
    'twitterAvatarUrl'  =>  $twitterAvatarUrl
);
?>
<?php echo $renderer->render_feed($config, $homeTimelineObj)?>
