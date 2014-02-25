<?php
/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   favoritesrss.php -- User favorites formatted as a RSS feed
 *   Version: 0.4.0
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

// include Christian Varga's twitter cache
require 'tldlib/tldCache.php';

/*******************************************************************
*  Defaults
********************************************************************/
$count = 25;  //default tweet number = 25
$screen_name = '';
$cache_interval = 90; // default cache interval = 90 seconds

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
        if (isset($params['cache_interval'])){
            $cache_interval = $params['cache_interval'];
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

    // cache_interval = the amount of time to keep the cached file
    if (isset($_GET["cache_interval"])){
        $cache_interval = $_GET["cache_interval"];
    }
} // end else

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
if ( $screen_name == '' ) $screen_name = $data['screen_name'];

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
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <atom:link href="<?php echo $my_domain ?><?php echo $thequery ?>" rel="self" type="application/rss+xml" />
        <lastBuildDate><?php echo date(DATE_RSS); ?></lastBuildDate>
        <language>en</language>
        <title>Twitter favorites feed for <?php echo $screen_name; ?></title>
        <description>Twitter favorites updates for <?php echo $screen_name; ?></description>
        <link>http://www.twitter.com/<?php echo $screen_name; ?></link>
        <ttl>960</ttl>
        <generator>Tweetledee</generator>
        <category>Personal</category>
        <image>
        <title>Twitter favorites updated for <?php echo $screen_name; ?></title>
        <link>http://www.twitter.com/<?php echo $screen_name; ?>/favorites</link>
        <url><?php echo $twitterAvatarUrl ?></url>
        </image>
        <?php foreach ($userFavoritesObj as $currentitem) : ?>
            <item>
                 <?php
                 $parsedTweet = tmhUtilities::entify_with_options(
                 		objectToArray($currentitem),
                 		array(
                 			'target' => 'blank',
                 		)
				 );

                if (isset($currentitem['retweeted_status'])) :
                    $avatar = $currentitem['retweeted_status']['user']['profile_image_url'];
                    $rt = '&nbsp;&nbsp;&nbsp;&nbsp;[<em style="font-size:smaller;">Retweeted by ' . $currentitem['user']['name'] . ' <a href=\'http://twitter.com/' . $currentitem['user']['screen_name'] . '\'>@' . $currentitem['user']['screen_name'] . '</a></em>]';
                    $tweeter =  $currentitem['retweeted_status']['user']['screen_name'];
                    $fullname = $currentitem['retweeted_status']['user']['name'];
                    $tweetTitle = $currentitem['retweeted_status']['text'];
                else :
                    $avatar = $currentitem['user']['profile_image_url'];
                    $rt = '';
                    $tweeter = $currentitem['user']['screen_name'];
                    $fullname = $currentitem['user']['name'];
                    $tweetTitle = $currentitem['text'];
               endif;
                ?>
				<title>[<?php echo $tweeter; ?>] <?php echo $tweetTitle; ?> </title>
                <pubDate><?php echo reformatDate($currentitem['created_at']); ?></pubDate>
                <link>https://twitter.com/<?php echo $tweeter ?>/statuses/<?php echo $currentitem['id_str']; ?></link>
                <guid isPermaLink='false'><?php echo $currentitem['id_str']; ?></guid>

                <description>
                    <![CDATA[
                        <div style='float:left;margin: 0 6px 6px 0;'>
							<a href='https://twitter.com/<?php echo $screen_name ?>/statuses/<?php echo $currentitem['id_str']; ?>' border=0 target='blank'>
								<img src='<?php echo $avatar; ?>' border=0 />
							</a>
						</div>
                        <strong><?php echo $fullname; ?></strong> <a href='https://twitter.com/<?php echo $tweeter; ?>' target='blank'>@<?php echo $tweeter;?></a><?php echo $rt ?><br />
                        <?php echo $parsedTweet; ?>
                    ]]>
               </description>
            </item>
        <?php endforeach; ?>
    </channel>
</rss>
