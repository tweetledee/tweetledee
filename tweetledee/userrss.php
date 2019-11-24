<?php
/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   userrss.php -- User timeline results formatted as a RSS feed
 * Copyright 2014 Christopher Simpkins
 * MIT License
 ************************************************************************************************/
/*-----------------------------------------------------------------------------------------------
==> Instructions:
    - place the tweetledee directory in the public facing directory on your web server (frequently public_html)
    - Access the default user timeline feed (count = 25, includes both RT's & replies) at the following URL:
            e.g. http://<yourdomain>/tweetledee/userrss.php
==> User Timeline RSS feed parameters:
    - 'c' - specify a tweet count (range 1 - 200, default = 25)
            e.g. http://<yourdomain>/tweetledee/userrss.php?c=100
    - 'user' - specify the Twitter user whose timeline you would like to retrieve (default = account associated with access token)
            e.g. http://<yourdomain>/tweetledee/userrss.php?user=cooluser
    - 'xrt' - exclude retweets (1=true, default = false)
            e.g. http://<yourdomain>/tweetledee/userrss.php?xrt=1
    - 'xrp' - exclude replies (1=true, default = false)
            e.g. http://<yourdomain>/tweetledee/userrss.php?xrp=1
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
    "exclude_retweets",
    "exclude_replies",
    "user",
    "cache_interval",
    "recursion_limit"
]);
extract($parameters);
$include_retweets = !$exclude_retweets;

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
if (!isset($screen_name) || $screen_name == '') {
    $screen_name = $data['screen_name'];
}

/*******************************************************************
*  Request
********************************************************************/

$userTimelineObj = $tldCache->user_request([
    'url' => '1.1/statuses/user_timeline',
    'params' => array(
        'include_entities' => true,
        'count' => $count,
        'exclude_replies' => $exclude_replies,
        'include_rts' => $include_retweets,
        'screen_name' => $screen_name,
    )
]);

// concatenate the URL for the atom href link
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
$config = array(
    'atom'              =>  $my_domain . $thequery,
    'link'              =>  sprintf('http://www.twitter.com/%s', $screen_name),
    'lastBuildDate'     =>  date(DATE_RSS),
    'title'             =>  sprintf('Twitter user timeline feed for %s', $screen_name),
    'description'       =>  sprintf('Twitter user timeline updates for %s', $screen_name),
    'twitterAvatarUrl'  =>  $twitterAvatarUrl
);
?>
<?php echo $renderer->render_feed($config, $userTimelineObj)?>
