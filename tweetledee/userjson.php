<?php
/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   userjson.php -- User timeline results formatted as JSON
 *   Version: 0.4.0
 * Copyright 2014 Christopher Simpkins
 * MIT License
 ************************************************************************************************/
/*-----------------------------------------------------------------------------------------------
==> Instructions:
    - place the tweetledee directory in the public facing directory on your web server (frequently public_html)
    - Access the default user timeline JSON (count = 25, includes both RT's & replies) at the following URL:
            e.g. http://<yourdomain>/tweetledee/userjson.php
==> User Timeline JSON parameters:
    - 'c' - specify a tweet count (range 1 - 200, default = 25)
            e.g. http://<yourdomain>/tweetledee/userjson.php?c=100
    - 'user' - specify the Twitter user whose timeline you would like to retrieve (default = account associated with access token)
            e.g. http://<yourdomain>/tweetledee/userjson.php?user=cooluser
    - 'xrt' - exclude retweets (1=true, default = false)
            e.g. http://<yourdomain>/tweetledee/userjson.php?xrt=1
    - 'xrp' - exclude replies (1=true, default = false)
            e.g. http://<yourdomain>/tweetledee/userjson.php?xrp=1
    - 'cache_interval' - specify the duration of the cache interval in seconds (default = 90sec)
--------------------------------------------------------------------------------------------------*/
/*******************************************************************
*  Debugging Flag
********************************************************************/
$TLD_DEBUG = 0;
if ($TLD_DEBUG == 1){
    ini_set('display_errors', 'On');
    error_reporting(E_ALL | E_STRICT);
}

/*******************************************************************
*  Client Side JavaScript Access Flag (default = 0 = off)
********************************************************************/
$TLD_JS = 0;
if ($TLD_JS == 1) {
    header('Access-Control-Allow-Origin: *');
}

/*******************************************************************
*  Includes
********************************************************************/

// Matt Harris' Twitter OAuth library
require 'tldlib/tmhOAuth.php';
require 'tldlib/tmhUtilities.php';

// include user keys
require 'tldlib/keys/tweetledee_keys.php';

// include Geoff Smith's utility functions
require 'tldlib/tldUtilities.php';

// include Christian Varga's twitter cache
require 'tldlib/tldCache.php';

/*******************************************************************
*  Defaults
********************************************************************/
$count = 25;  //default tweet number = 25
$include_retweets = true;  //default to include retweets
$exclude_replies = false;  //default to include replies
$screen_name = '';
$cache_interval = 90; // default cache interval = 90 seconds

/*******************************************************************
*   Parameters
*    - can pass via URL to web server
*    - or as a parameter at the command line
********************************************************************/

// Command line parameter definitions //
if (defined('STDIN')) {
    // check whether arguments were passed, if not there is no need to attempt to check the array
    if (isset($argv)){
        $shortopts = "c:";
        $longopts = array(
            "xrt",
            "xrp",
            "user:",
        );
        $params = getopt($shortopts, $longopts);
        if (isset($params['c'])){
            if ($params['c'] > 0 && $params['c'] <= 200)
                $count = $params['c'];  //assign to the count variable
        }
        if (isset($params['xrt'])){
            $include_retweets = false;
        }
        if (isset($params['xrp'])){
            $exclude_replies = true;
        }
        if (isset($params['user'])){
            $screen_name = $params['user'];
        }
        if (isset($params['cache_interval'])){
            $cache_interval = $params['cache_interval'];
        }
    }
}
// Web server URL parameter definitions //
else {
    // c = tweet count ( possible range 1 - 200 tweets, else default = 25)
    if (isset($_GET["c"])){
        if ($_GET["c"] > 0 && $_GET["c"] <= 200){
            $count = $_GET["c"];
        }
    }
    // xrt = exclude retweets from the timeline ( possible values: 1=true, else false)
    if (isset($_GET["xrt"])){
        if ($_GET["xrt"] == 1){
            $include_retweets = false;
        }
    }
    // xrp = exclude replies from the timeline (possible values: 1=true, else false)
    if (isset($_GET["xrp"])){
        if ($_GET["xrp"] == 1){
            $exclude_replies = true;
        }
    }
    // user = Twitter screen name for the user timeline that the user is requesting (default = their own, possible values = any other Twitter user name)
    if (isset($_GET["user"])){
        $screen_name = $_GET["user"];
    }
    // cache_interval = the amount of time to keep the cached file
    if (isset($_GET["cache_interval"])){
        $cache_interval = $_GET["cache_interval"];
    }
} // end else block

/*******************************************************************
*  OAuth
********************************************************************/

$tldCache = new tldCache(array(
            'consumer_key'        => $my_consumer_key,
            'consumer_secret'     => $my_consumer_secret,
            'user_token'          => $my_access_token,
            'user_secret'         => $my_access_token_secret,
            'curl_ssl_verifypeer' => false
        ), $cache_interval);

// request the user information
$data = $tldCache->auth_request();

// Parse information from response
$twitterName = $data['screen_name'];
$fullName = $data['name'];
$twitterAvatarUrl = $data['profile_image_url'];

if ( $screen_name == '' ) $screen_name = $data['screen_name'];

/*******************************************************************
*  Request
********************************************************************/

$userTimelineObj = $tldCache->user_request(array(
            'url' => '1.1/statuses/user_timeline',
            'params' => array(
                'include_entities' => true,
                'count' => $count,
                'exclude_replies' => $exclude_replies,
                'include_rts' => $include_retweets,
                'screen_name' => $screen_name,
            )
        ));

header('Content-Type: application/json');
echo json_encode($userTimelineObj);

