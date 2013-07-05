<?php
/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   listsjson.php -- User list tweets formatted as JSON
 *   Version: 0.2.9
 * Copyright 2013 Christopher Simpkins
 * MIT License
 ************************************************************************************************/
/*-----------------------------------------------------------------------------------------------
==> Instructions:
    - place the tweetledee directory in the public facing directory on your web server (frequently public_html)
    - Access the default user list feed (count = 25, includes both RT's & replies) at the following URL:
            e.g. http://<yourdomain>/tweetledee/listsjson.php?list=<list-slug>
==> User Favorites RSS feed parameters:
    - 'c' - specify a tweet count (range 1 - 200, default = 25)
            e.g. http://<yourdomain>/tweetledee/listsjson.php?c=100
    - 'list' - the list name for the specified user (default = account associated with access token)
            e.g. http://<yourdomain>/tweetledee/listsjson.php?list=theblacklist
    - 'user' - specify the Twitter user whose favorites you would like to retrieve (default = account associated with access token)
            e.g. http://<yourdomain>/tweetledee/listsjson.php?user=cooluser
    - 'xrt' - exclude retweets in the returned data (set to 1 to exclude, default = include retweets)
    - Example of all of the available parameters:
            e.g. http://<yourdomain>/tweetledee/listsjson.php?c=100&user=cooluser
--------------------------------------------------------------------------------------------------*/
// debugging
$TLD_DEBUG = 1;
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

// Mandatory URL parameter - do not execute the OAuth request if this is missing
// list = list name for a list owned by the specified user (or if no user specified, the user associated with the access token account)
if (isset($_GET["list"])){
    $list_name = $_GET["list"];
}
else{
    die("Error: missing user list name in your request (use the 'list' URL parameter).");
}

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

// Parse information from response
$twitterName = $data['screen_name'];
$fullName = $data['name'];
$twitterAvatarUrl = $data['profile_image_url'];

// Defaults
$count = 25;  //default tweet number = 25
$include_retweets = true;  //default to include retweets
$screen_name = $data['screen_name'];

// Parameters
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

// request the user favorites using the paramaters that were parsed from URL or that are defaults
$code = $tmhOAuth->user_request(array(
			'url' => $tmhOAuth->url('1.1/lists/statuses'),
			'params' => array(
          		'include_entities' => true,
    			'count' => $count,
    			'owner_screen_name' => $screen_name,
                'slug' => $list_name,
                'include_rts' => $include_retweets,
        	)
        ));

// Anything except code 200 is a failure to get the information
if ($code <> 200) {
    echo $tmhOAuth->response['error'];
    echo("Please confirm that you included the required URL parameters and the correct list slug.");
    die(" (user_list connection failure)");
}

$userListObj = json_decode($tmhOAuth->response['response'], true);

header('Content-Type: application/json');
echo json_encode($userListObj);

