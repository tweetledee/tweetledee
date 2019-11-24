<?php
/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   listsjson_pp_nocache.php -- User list tweets formatted as pretty printed JSON
 * Copyright 2014 Christopher Simpkins
 * MIT License
 ************************************************************************************************/
/*-----------------------------------------------------------------------------------------------
==> Instructions:
    - place the tweetledee directory in the public facing directory on your web server (frequently public_html)
    - Access the default user list feed (count = 25, includes both RT's & replies) at the following URL:
            e.g. http://<yourdomain>/tweetledee/listsjson_pp_nocache.php?list=<list-slug>
==> User List Pretty Printed JSON parameters:
    - 'c' - specify a tweet count (range 1 - 200, default = 25)
            e.g. http://<yourdomain>/tweetledee/listsjson_pp_nocache.php?list=<list-slug>&c=100
    - 'list' - the list name for the specified user (default = account associated with access token)
            e.g. http://<yourdomain>/tweetledee/listsjson_pp_nocache.php?list=theblacklist
    - 'user' - specify the Twitter user whose favorites you would like to retrieve (default = account associated with access token)
            e.g. http://<yourdomain>/tweetledee/listsjson_pp_nocache.php?list=<list-slug>&user=cooluser
    - 'xrt' - exclude retweets in the returned data (set to 1 to exclude, default = include retweets)
            e.g. http://<yourdomain>/tweetledee/listsjson_pp_nocache.php?list=<list-slug>&xrt=1
    - Example of all of the available parameters:
            e.g. http://<yourdomain>/tweetledee/listsjson_pp_nocache.php?c=100&user=santaclaus&list=nicelist&xrt=1
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

// include Martín Lucas Golini's pretty print functions
require 'tldlib/tldPrettyPrint.php';

require 'tldlib/parametersProcessing.php';

$parameters = load_parameters(array("c", "user", "exclude_retweets", "list"));
extract($parameters);
if(!isset($parameters['list'])) {
    die("Error: missing user list name in your request.  Please use the 'list' parameter in your request.");
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

// Parse information from response
$twitterName = $data['screen_name'];
$fullName = $data['name'];
$twitterAvatarUrl = $data['profile_image_url'];

if(!isset($screen_name) || $screen_name=='') {
    $screen_name = $data['screen_name'];
}

/*******************************************************************
*  Defaults
********************************************************************/
/*******************************************************************
*  Request
********************************************************************/
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
echo json_encode_pretty_print($userListObj);
