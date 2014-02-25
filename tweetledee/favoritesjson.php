<?php
/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   favoritesjson.php -- User favorites formatted as JSON
 *   Version: 0.4.0
 * Copyright 2014 Christopher Simpkins
 * MIT License
 ************************************************************************************************/
/*-----------------------------------------------------------------------------------------------
==> Instructions:
    - place the tweetledee directory in the public facing directory on your web server (frequently public_html)
    - Access the default user favorites feed (count = 25, includes both RT's & replies) at the following URL:
            e.g. http://<yourdomain>/tweetledee/favoritesjson.php
==> User Favorites JSON parameters:
    - 'c' - specify a tweet count (range 1 - 200, default = 25)
            e.g. http://<yourdomain>/tweetledee/favoritesjson.php?c=100
    - 'user' - specify the Twitter user whose favorites you would like to retrieve (default = account associated with access token)
            e.g. http://<yourdomain>/tweetledee/favoritesjson.php?user=cooluser
    - 'cache_interval' - specify the duration of the cache interval in seconds (default = 90sec)
--------------------------------------------------------------------------------------------------*/

/*******************************************************************
*  Debugging Flag (default = 0 = off)
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
$screen_name = '';
$cache_interval = 90; // default cache interval = 90 seconds

/*******************************************************************
*   Parameters
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
        );
        $params = getopt($shortopts, $longopts);
        if (isset($params['c'])){
            if ($params['c'] > 0 && $params['c'] <= 200)
                $count = $params['c'];  //assign to the count variable
        }
        if (isset($params['user'])){
            $screen_name = $params['user'];
        }
        if (isset($params['cache_interval'])){
            $cache_interval = $params['cache_interval'];
        }
    }
}
else {
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

    // cache_interval = the amount of time to keep the cached file
    if (isset($_GET["cache_interval"])){
        $cache_interval = $_GET["cache_interval"];
    }
} // end else

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
$userFavoritesObj = $tldCache->user_request(array(
            'url' => '1.1/favorites/list',
            'params' => array(
                'include_entities' => true,
                'count' => $count,
                'screen_name' => $screen_name,
            )
        ));

header('Content-Type: application/json');
echo json_encode($userFavoritesObj);
