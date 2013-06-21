<?php
/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   userjson.php -- User timeline results formatted as JSON
 *   Version: 0.2.6
 * Copyright 2013 Christopher Simpkins
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
    - Example of all of the available parameters:
            e.g. http://<yourdomain>/tweetledee/userjson.php?c=100&xrt=1&xrp=1&user=cooluser
--------------------------------------------------------------------------------------------------*/

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
    echo $tmhOAuth->response['error'];
    die("verify_credentials connection failure");
}

// Decode JSON
$data = json_decode($tmhOAuth->response['response'], true);

// Defaults
$count = 25;  //default tweet number = 25
$include_retweets = true;  //default to include retweets
$exclude_replies = false;  //default to include replies
$screen_name = $data['screen_name'];

// Parameters
// c = tweet count ( possible range 1 - 200 tweets, else default = 25)
$getcount = $_GET["c"];
if ($getcount > 0 && $getcount <= 200){
	$count = $getcount;
}
// xrt = exclude retweets from the timeline ( possible values: 1=true, else false)
if ($_GET["xrt"] == 1){
	$include_retweets = false;
}
// xrp = exclude replies from the timeline (possible values: 1=true, else false)
if ($_GET["xrp"] == 1){
	$exclude_replies = true;
}

// user = Twitter screen name for the user timeline that the user is requesting (default = their own, possible values = any other Twitter user name)
$getuser = $_GET["user"];
if ($getuser != NULL){
	$screen_name = $getuser;
}

// request the user timeline using the paramaters that were parsed from URL or that are defaults
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
echo json_encode($userTimelineObj, JSON_PRETTY_PRINT);

?>

