<?php
/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   listsjson_pp.php -- User list tweets formatted as pretty printed JSON
 *   Version: 0.4.0
 * Copyright 2014 Christopher Simpkins
 * MIT License
 ************************************************************************************************/
/*-----------------------------------------------------------------------------------------------
==> Instructions:
    - place the tweetledee directory in the public facing directory on your web server (frequently public_html)
    - Access the default user list feed (count = 25, includes both RT's & replies) at the following URL:
            e.g. http://<yourdomain>/tweetledee/listsjson_pp.php?list=<list-slug>
==> User List Pretty Printed JSON parameters:
    - 'c' - specify a tweet count (range 1 - 200, default = 25)
            e.g. http://<yourdomain>/tweetledee/listsjson_pp.php?list=<list-slug>&c=100
    - 'list' - the list name for the specified user (default = account associated with access token)
            e.g. http://<yourdomain>/tweetledee/listsjson_pp.php?list=theblacklist
    - 'user' - specify the Twitter user whose favorites you would like to retrieve (default = account associated with access token)
            e.g. http://<yourdomain>/tweetledee/listsjson_pp.php?list=<list-slug>&user=cooluser
    - 'xrt' - exclude retweets in the returned data (set to 1 to exclude, default = include retweets)
            e.g. http://<yourdomain>/tweetledee/listsjson_pp.php?list=<list-slug>&xrt=1
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

/***************************************************************************************
*  Mandatory parameter (list)
*   - do not execute the OAuth authentication request if missing (keep before OAuth code)
****************************************************************************************/
// list = list name for a list owned by the specified user (or if no user specified, the user associated with the access token account)
if (isset($_GET["list"])){
    $list_name = $_GET["list"];
}
else if (defined('STDIN')) {
    if (isset($argv)){
        $shortopts = "";
        $longopts = array(
            "list:",
        );
    }
    else {
        die("Error: missing user list name in your request.  Please use the 'list' parameter in your request.");
    }
    $params = getopt($shortopts, $longopts);
    if (isset($params['list'])){
        $list_name = $params['list'];
    }
    else{
        die("Error: unable to parse the user list name in your request.  Please use the 'list' parameter in your request.");
    }
}
else{
    die("Error: missing user list name in your request.  Please use the 'list' parameter in your request.");
}

/*******************************************************************
*  Defaults
********************************************************************/
$count = 25;  //default tweet number = 25
$include_retweets = true;  //default to include retweets
$screen_name = '';
$cache_interval = 90; // default cache interval = 90 seconds

/*******************************************************************
*   Optional Parameters
*    - can pass via URL to web server
*    - or as a short or long switch at the command line
********************************************************************/
// Command line parameter definitions //
if (defined('STDIN')) {
    // check whether arguments were passed, if not there is no need to attempt to check the array
    if (isset($argv)){
        $shortopts = "c:";
        $longopts = array(
            "user:",
            "xrt",
        );
        $params = getopt($shortopts, $longopts);
        if (isset($params['c'])){
            if ($params['c'] > 0 && $params['c'] <= 200)
                $count = $params['c'];  //assign to the count variable
        }
        if (isset($params['user'])){
            $screen_name = $params['user'];
        }
        if (isset($params['xrt'])){
            $include_retweets = false;
        }
        if (isset($params['cache_interval'])){
            $cache_interval = $params['cache_interval'];
        }
    }
} //end if defined 'stdin'
// Web server URL parameter definitions //
else{
    // c = tweet count ( possible range 1 - 200 tweets, else default = 25)
    if (isset($_GET["c"])){
        if ($_GET["c"] > 0 && $_GET["c"] <= 200){
            $count = $_GET["c"];
        }
    }

    // user = Twitter screen name for the user favorites that the user is requesting (default = their own, possible values = any other Twitter user name)
    if (isset($_GET["user"])){
        $screen_name = $_GET["user"];
    }

    // xrt = exclude retweets
    if (isset($_GET["xrt"])){
        $include_retweets = false;
    }

    // cache_interval = the amount of time to keep the cached file
    if (isset($_GET["cache_interval"])){
        $cache_interval = $_GET["cache_interval"];
    }
} //end else

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
if ( $screen_name == '' ) $screen_name = $data['screen_name'];
$fullName = $data['name'];
$twitterAvatarUrl = $data['profile_image_url'];

/*******************************************************************
*  Request
********************************************************************/

$userListObj = $tldCache->user_request(array(
            'url' => '1.1/lists/statuses',
            'params' => array(
                'include_entities' => true,
                'count' => $count,
                'owner_screen_name' => $screen_name,
                'slug' => $list_name,
                'include_rts' => $include_retweets,
            )
        ));

header('Content-Type: application/json');
echo json_encode($userListObj, JSON_PRETTY_PRINT);

