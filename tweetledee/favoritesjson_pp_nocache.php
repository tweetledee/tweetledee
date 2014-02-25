<?php
/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   favoritesjson_pp_nocache.php -- User favorites formatted as pretty printed JSON
 *   Version: 0.4.0
 * Copyright 2014 Christopher Simpkins
 * MIT License
 ************************************************************************************************/
/*-----------------------------------------------------------------------------------------------
==> Instructions:
    - place the tweetledee directory in the public facing directory on your web server (frequently public_html)
    - Access the default user favorites feed (count = 25, includes both RT's & replies) at the following URL:
            e.g. http://<yourdomain>/tweetledee/favoritesjson_pp_nocache.php
==> User Favorites JSON parameters:
    - 'c' - specify a tweet count (range 1 - 200, default = 25)
            e.g. http://<yourdomain>/tweetledee/favoritesjson_pp_nocache.php?c=100
    - 'user' - specify the Twitter user whose favorites you would like to retrieve (default = account associated with access token)
            e.g. http://<yourdomain>/tweetledee/favoritesjson_pp_nocache.php?user=cooluser
    - Example of all of the available parameters:
            e.g. http://<yourdomain>/tweetledee/favoritesjson_pp_nocache.php?c=100&user=cooluser
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

/*******************************************************************
*  OAuth
********************************************************************/
$tmhOAuth = new tmhOAuth(array(
            'consumer_key'        => $my_consumer_key,
            'consumer_secret'     => $my_consumer_secret,
            'user_token'          => $my_access_token,
            'user_secret'         => $my_access_token_secret,
            'curl_ssl_verifypeer' => false
        ));

// request the user information
$code = $tmhOAuth->user_request(array(
            'url' => $tmhOAuth->url('1.1/account/verify_credentials')
          )
        );

// Display error response if do not receive 200 response code
if ($code <> 200) {
    if ($code == 429) {
        die("Exceeded Twitter API rate limit");
    }
    echo $tmhOAuth->response['error'];
    die("verify_credentials connection failure");
}

// Decode JSON
$data = json_decode($tmhOAuth->response['response'], true);

/*******************************************************************
*  Defaults
********************************************************************/
$count = 25;  //default tweet number = 25
$screen_name = $data['screen_name'];  //default is the requesting user

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
} // end else

/*******************************************************************
*  Request
********************************************************************/
$code = $tmhOAuth->user_request(array(
            'url' => $tmhOAuth->url('1.1/favorites/list'),
            'params' => array(
                'include_entities' => true,
                'count' => $count,
                'screen_name' => $screen_name,
            )
        ));

// Anything except code 200 is a failure to get the information
if ($code <> 200) {
    echo $tmhOAuth->response['error'];
    die("user_favorites connection failure");
}

$userFavoritesObj = json_decode($tmhOAuth->response['response'], true);
header('Content-Type: application/json');
echo json_encode($userFavoritesObj, JSON_PRETTY_PRINT);
