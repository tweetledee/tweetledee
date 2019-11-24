<?php
/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   searchjson_nocache.php -- Tweet search query results formatted as JSON
 * Copyright 2014 Christopher Simpkins
 * MIT License
 ************************************************************************************************/
/*-----------------------------------------------------------------------------------------------
==> Instructions:
    - place the tweetledee directory in the public facing directory on your web server (frequently public_html)
    - Generic tweet search JSON URL (count = 25):
            e.g. http://<yourdomain>/tweetledee/searchjson_nocache.php?q=<search-term>
==> Twitter Tweet Search JSON parameters:
    - 'c' - specify a tweet count (range 1 - 200, default = 25)
            e.g. http://<yourdomain>/tweetledee/searchjson_nocache.php?q=<search-term>&c=100
    - 'rt' - result type (possible values: mixed, recent, popular; default = mixed)
            e.g. http://<yourdomain>/tweetledee/searchjson_nocache.php?q=<search-term>&rt=recent
    - 'q' - query term
            e.g. http://<yourdomain>/tweetledee/searchjson_nocache.php?q=coolsearch
    -  Example of all parameters
            http://<yourdomain>/tweetledee/searchjson_nocache.php?q=coolsearch&c=50&rt=recent
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

require 'tldlib/parametersProcessing.php';

$parameters = load_parameters(array("c", "q", "rt"));
extract($parameters);
if(!isset($parameters['q'])) {
    die("Error: missing the search query term.  Please use the 'q' parameter.");
}

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

//url encode the search query
//$urlquery = urlencode($query);

/*******************************************************************
*  Request
********************************************************************/
$code = $tmhOAuth->user_request(array(
			'url' => $tmhOAuth->url('1.1/search/tweets'),
			'params' => array(
          		'include_entities' => true,
    			'count' => $count,
                'result_type' => $result_type,
                'q' => $query,
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
