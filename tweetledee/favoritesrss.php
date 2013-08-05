<?php
/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   favoritesrss.php -- User favorites formatted as a RSS feed
 *   Version: 0.3.2
 * Copyright 2013 Christopher Simpkins & George Dorn
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
    - Example of all of the available parameters:
            e.g. http://<yourdomain>/tweetledee/favoritesrss.php?c=100&user=cooluser
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

// Parse information from response
$twitterName = $data['screen_name'];
$fullName = $data['name'];
$twitterAvatarUrl = $data['profile_image_url'];

/*******************************************************************
*  Defaults
********************************************************************/
$count = 25;  //default tweet number = 25
$screen_name = $data['screen_name'];  //default is the requesting user

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
} // end else

/*******************************************************************
*  Request
********************************************************************/
$code = $tmhOAuth->user_request(array(
			'url' => $tmhOAuth->url('1.1/favorites/list'),
			'params' => array(
          		'include_entities' => true,
    			'count' => $count,
    			'screen_name' => $screen_name,
        	)
        ));

// Anything except code 200 is a failure to get the information
if ($code <> 200) {
    echo $tmhOAuth->response['error'];
    die("user_favorites connection failure");
}

//concatenate the URL for the atom href link
$thequery = $_SERVER['PHP_SELF'] .'?'. urlencode($_SERVER['QUERY_STRING']);

$userFavoritesObj = json_decode($tmhOAuth->response['response'], true);

// Start the output
header("Content-Type: application/rss+xml");
header("Content-type: text/xml; charset=utf-8");
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <atom:link href="<?= $my_domain ?><?= $thequery ?>" rel="self" type="application/rss+xml" />
        <lastBuildDate><?= date(DATE_RSS); ?></lastBuildDate>
        <language>en</language>
        <title>Twitter favorites feed for <?= $screen_name; ?></title>
        <description>
            Twitter favorites  updates for <?= $screen_name; ?>.
        </description>
        <link>http://www.twitter.com/<?= $screen_name; ?></link>
        <ttl>960</ttl>
        <generator>Tweetledee</generator>
        <category>Personal</category>
        <image>
        <title>Twitter Favorites for <?= $screen_name; ?></title>
        <link>http://www.twitter.com/<?= $screen_name; ?>/favorites</link>
        <url><?= $twitterAvatarUrl ?></url>
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
				<title>[<?= $tweeter; ?>] <?= $tweetTitle; ?> </title>
                <pubDate><?= reformatDate($currentitem['created_at']); ?></pubDate>
                <link>https://twitter.com/<?= $tweeter ?>/statuses/<?= $currentitem['id_str']; ?></link>
                <guid isPermaLink='false'><?= $currentitem['id_str']; ?></guid>

                <description>
                    <![CDATA[
                        <div style='float:left;margin: 0 6px 6px 0;'>
							<a href='https://twitter.com/<?= $screen_name ?>/statuses/<?= $currentitem['id_str']; ?>' border=0 target='blank'>
								<img src='<?= $avatar; ?>' border=0 />
							</a>
						</div>
                        <strong><?= $fullname; ?></strong> <a href='https://twitter.com/<?= $tweeter; ?>' target='blank'>@<?= $tweeter;?></a><?= $rt ?><br />
                        <?= $parsedTweet; ?>
                    ]]>
               </description>
            </item>
        <?php endforeach; ?>
    </channel>
</rss>
