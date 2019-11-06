<?php
/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   favoritesrss.php -- User favorites formatted as a RSS feed
 *   Version: 0.5.0
 * Copyright 2014 Christopher Simpkins & George Dorn
 * MIT License
 ************************************************************************************************/
/*-----------------------------------------------------------------------------------------------
==> Instructions:
    - place the tweetledee directory in the public facing directory on your web server (frequently public_html)
    - Access the default user favorites feed (count = 25, includes both RT's & replies) at the following URL:
            e.g. http://<yourdomain>/tweetledee/favoritesrss.php
==> User Favorites RSS feed parameters:
    - 'c' - specify a tweet count (range 1 - 200, default = 25)
            e.g. http://<yourdomain>/tweetledee/favoritesrss.php?c=100
    - 'user' - specify the Twitter user whose favorites you would like to retrieve (default = account associated with access token)
            e.g. http://<yourdomain>/tweetledee/favoritesrss.php?user=cooluser
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

require 'tldlib/renderers/rss.php';

require 'tldlib/parametersProcessing.php';

$parameters = load_parameters(array("c", "user", "cache_interval"));
extract($parameters);
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
if(!isset($screen_name) || $screen_name=='') {
    $screen_name = $data['screen_name'];
}

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

//concatenate the URL for the atom href link
if (defined('STDIN')) {
    $thequery = $_SERVER['PHP_SELF'];
} else {
    $thequery = $_SERVER['PHP_SELF'] .'?'. urlencode($_SERVER['QUERY_STRING']);
}

// Start the output
header("Content-Type: application/rss+xml");
header("Content-type: text/xml; charset=utf-8");

$renderer = new RssRenderer();
$config = array(
    'atom'              =>  $my_domain . $thequery,
    'link'              =>  sprintf('http://www.twitter.com/%s', $screen_name),
    'lastBuildDate'     =>  date(DATE_RSS),
    'title'             =>  sprintf('Twitter favorites feed for %s', $screen_name),
    'description'       =>  sprintf('Twitter favorites feed for %s', $screen_name),
    'twitterAvatarUrl'  =>  $twitterAvatarUrl
);
?>
<?php echo $renderer->render_feed($config, $userFavoritesObj)?>
