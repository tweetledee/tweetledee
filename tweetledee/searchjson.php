<?php
/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   searchjson.php -- Search query results formatted as JSON
 *   Version: 0.2.8
 * Copyright 2013 Christopher Simpkins
 * MIT License
 ************************************************************************************************/
/*-----------------------------------------------------------------------------------------------
==> Instructions:
    - place the tweetledee directory in the public facing directory on your web server (frequently public_html)
    - Access the default user timeline feed (count = 25, includes both RT's & replies) at the following URL:
            e.g. http://<yourdomain>/tweetledee/userrss.php
==> User Timeline RSS feed parameters:
    - 'c' - specify a tweet count (range 1 - 200, default = 25)
    - 'rt' - result type (possible values: mixed, recent, popular; default = mixed)
    - 'q' - query term
            e.g. http://<yourdomain>/tweetledee/searchrss.php?q=coolsearch&c=50
--------------------------------------------------------------------------------------------------*/
// debugging
$TLD_DEBUG = 0;
if ($TLD_DEBUG == 1){
    ini_set('display_errors', 'On');
    error_reporting(E_ALL | E_STRICT);
}

// Matt Harris' Twitter OAuth library
require 'tldlib/tmhOAuth.php';
require 'tldlib/tmhUtilities.php';

// include user keys
require 'tldlib/keys/tweetledee_keys.php';

// include Geoff Smith's utility functions
require 'tldlib/tldUtilities.php';

// create the OAuth object
///*
$tmhOAuth = new tmhOAuth(array(
            'consumer_key'        => $my_consumer_key,
            'consumer_secret'     => $my_consumer_secret,
            'user_token'          => $my_access_token,
            'user_secret'         => $my_access_token_secret,
            'curl_ssl_verifypeer' => false
        ));
//*/

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

// Defaults
$count = 25;  //default tweet number = 25
$result_type = 'mixed'; //default to mixed popular and realtime results

// Parameters
// c = tweet count ( possible range 1 - 200 tweets, else default = 25)
if (isset($_GET["c"])){
    if ($_GET["c"] > 0 && $_GET["c"] <= 200){
        $count = $_GET["c"];
    }
}
// rt = response type
if (isset($_GET["rt"])){
    if ($_GET["rt"] == 'popular' || $_GET["rt"] == 'recent'){
        $result_type = $_GET["rt"];
    }
}
// q = query
if (isset($_GET["q"])){
    $query = $_GET["q"];
}
else{
    echo("The request was missing a search query");
    die(" - search query missing failure");
}

$urlquery = urlencode($query);

// request the user timeline using the paramaters that were parsed from URL or that are defaults
$code = $tmhOAuth->user_request(array(
			'url' => $tmhOAuth->url('1.1/search/tweets'),
			'params' => array(
          		'include_entities' => true,
    			'count' => $count,
                'result_type' => $result_type,
                'q' => $urlquery,
        	)
        ));

// Anything except code 200 is a failure to get the information
if ($code <> 200) {
    echo $tmhOAuth->response['error'];
    die("tweet_search connection failure");
}

$searchResultsObj = json_decode($tmhOAuth->response['response'], true);
header('Content-Type: application/json');
echo json_encode($searchResultsObj);
