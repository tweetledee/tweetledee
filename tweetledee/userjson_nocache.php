<?php
/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   userjson_nocache.php -- User timeline results formatted as JSON
 * Copyright 2014 Christopher Simpkins
 * MIT License
 ************************************************************************************************/
/*-----------------------------------------------------------------------------------------------
==> Instructions:
    - place the tweetledee directory in the public facing directory on your web server (frequently public_html)
    - Access the default user timeline JSON (count = 25, includes both RT's & replies) at the following URL:
            e.g. http://<yourdomain>/tweetledee/userjson_nocache.php
==> User Timeline JSON parameters:
    - 'c' - specify a tweet count (range 1 - 200, default = 25)
            e.g. http://<yourdomain>/tweetledee/userjson_nocache.php?c=100
    - 'user' - specify the Twitter user whose timeline you would like to retrieve (default = account associated with access token)
            e.g. http://<yourdomain>/tweetledee/userjson_nocache.php?user=cooluser
    - 'xrt' - exclude retweets (1=true, default = false)
            e.g. http://<yourdomain>/tweetledee/userjson_nocache.php?xrt=1
    - 'xrp' - exclude replies (1=true, default = false)
            e.g. http://<yourdomain>/tweetledee/userjson_nocache.php?xrp=1
    - Example of all of the available parameters:
            e.g. http://<yourdomain>/tweetledee/userjson_nocache.php?c=100&xrt=1&xrp=1&user=cooluser
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

$parameters = load_parameters(array("c", "exclude_retweets", "exclude_replies", "user"));
extract($parameters);
$include_retweets = !$exclude_retweets;

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
if(!isset($screen_name) || $screen_name=='') {
    $screen_name = $data['screen_name'];
}

/*******************************************************************
*  Request
********************************************************************/
// request the user timeline using the parameters that were parsed from URL or that are defaults
$code = $tmhOAuth->user_request(array(
			'url' => $tmhOAuth->url('1.1/statuses/user_timeline'),
			'params' => array(
          		'include_entities' => true,
    			'count' => $count,
    			'exclude_replies' => $exclude_replies,
    			'include_rts' => $include_retweets,
    			'screen_name' => $screen_name,
        	)
        ));

// Anything except code 200 is a failure to get the information
if ($code <> 200) {
    echo $tmhOAuth->response['error'];
    die("user_timeline connection failure");
}

$userTimelineObj = json_decode($tmhOAuth->response['response'], true);
header('Content-Type: application/json');
echo json_encode($userTimelineObj);
