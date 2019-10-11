<?php
/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   searchjson.php -- Tweet search query results formatted as JSON
 *   Version: 0.5.0
 * Copyright 2014 Christopher Simpkins
 * MIT License
 ************************************************************************************************/
/*-----------------------------------------------------------------------------------------------
==> Instructions:
    - place the tweetledee directory in the public facing directory on your web server (frequently public_html)
    - Generic tweet search JSON URL (count = 25):
            e.g. http://<yourdomain>/tweetledee/searchjson.php?q=<search-term>
==> Twitter Tweet Search JSON parameters:
    - 'c' - specify a tweet count (range 1 - 200, default = 25)
            e.g. http://<yourdomain>/tweetledee/searchjson.php?q=<search-term>&c=100
    - 'rt' - result type (possible values: mixed, recent, popular; default = mixed)
            e.g. http://<yourdomain>/tweetledee/searchjson.php?q=<search-term>&rt=recent
    - 'q' - query term
            e.g. http://<yourdomain>/tweetledee/searchjson.php?q=coolsearch
    - 'cache_interval' - specify the duration of the cache interval in seconds (default = 90sec)
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

require 'tldlib/parametersProcessing.php';

$parameters = load_parameters(array("c", "q", "rt", "cache_interval"));
extract($parameters);
if(!isset($parameters['q'])) {
    die("Error: missing the search query term.  Please use the 'q' parameter.");
}

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

//Create the feed title with the query
$feedTitle = 'Twitter search for "' . $query . '"';

// URL encode the search query
//$urlquery = urlencode($query);

/*******************************************************************
*  Request
********************************************************************/
$searchResultsObj = $tldCache->user_request(array(
            'url' => '1.1/search/tweets',
            'params' => array(
                'include_entities' => true,
                'count' => $count,
                'result_type' => $result_type,
                'q' => $query,
            )
        ));

header('Content-Type: application/json');
echo json_encode($searchResultsObj);
