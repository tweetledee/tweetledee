<?php
/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   homejson_pp_nocache.php -- Home timeline results formatted as pretty printed JSON
 *   Version: 0.4.0
 * Copyright 2014 Christopher Simpkins
 * MIT License
 ************************************************************************************************/
/*-----------------------------------------------------------------------------------------------
==> Instructions:
    - place the tweetledee directory in the public facing directory on your web server (frequently public_html)
    - Access the default home timeline JSON (count = 25 & includes replies) at the following URL:
            e.g. http://<yourdomain>/tweetledee/homejson_pp_nocache.php
==> User's Home Timeline Pretty Printed JSON parameters:
    - 'c' - specify a tweet count (range 1 - 200, default = 25)
            e.g. http://<yourdomain>/tweetledee/homejson_pp_nocache.php?c=100
    - 'xrp' - exclude replies (1=true, default = false)
            e.g. http://<yourdomain>/tweetledee/homejson_pp_nocache.php?xrp=1
    - Example of all of the available parameters:
            e.g. http://<yourdomain>/tweetledee/homejson_pp_nocache.php?c=100&xrp=1
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
$exclude_replies = false;  //default to include replies
$screen_name = $data['screen_name'];

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
            "xrp",
        );
        $params = getopt($shortopts, $longopts);
        if (isset($params['c'])){
            if ($params['c'] > 0 && $params['c'] <= 200)
                $count = $params['c'];  //assign to the count variable
        }
        if (isset($params['xrp'])){
            $exclude_replies = true;
        }
    }

} //end if
// Web server URL parameter definitions //
else{
    // c = tweet count ( possible range 1 - 200 tweets, else default = 25)
    if (isset($_GET["c"])){
        $getcount = $_GET["c"];
        if ($getcount > 0 && $getcount <= 200){
            $count = $getcount;
        }
    }

    // xrp = exclude replies from the timeline (possible values: 1=true, else false)
    if (isset($_GET["xrp"])){
        if ($_GET["xrp"] == 1){
            $exclude_replies = true;
        }
    }
} //end else

/*******************************************************************
*  Request
********************************************************************/
$code = $tmhOAuth->user_request(array(
			'url' => $tmhOAuth->url('1.1/statuses/home_timeline'),
			'params' => array(
          		'include_entities' => true,
    			'count' => $count,
    			'exclude_replies' => $exclude_replies,
        	)
        ));

// Anything except code 200 is a failure to get the information
if ($code <> 200) {
    echo $tmhOAuth->response['error'];
    die("home_timeline connection failure");
}

$homeTimelineObj = json_decode($tmhOAuth->response['response'], true);
header('Content-Type: application/json');
echo json_encode($homeTimelineObj, JSON_PRETTY_PRINT);
