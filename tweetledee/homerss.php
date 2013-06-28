<?php
/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   homerss.php -- Home timeline results formatted as RSS feed
 *   Version: 0.2.8
 * Copyright 2013 Christopher Simpkins
 * MIT License
 ************************************************************************************************/
/*-----------------------------------------------------------------------------------------------
==> Instructions:
    - place the tweetledee directory in the public facing directory on your web server (frequently public_html)
    - Access the default home timeline feed (count = 25, includes both RT's & replies) at the following URL:
            e.g. http://<yourdomain>/tweetledee/homerss.php
==> User Timeline RSS feed parameters:
    - 'c' - specify a tweet count (range 1 - 200, default = 25)
            e.g. http://<yourdomain>/tweetledee/homerss.php?c=100
    - 'xrp' - exclude replies (1=true, default = false)
            e.g. http://<yourdomain>/tweetledee/homerss.php?xrp=1
    - Example of all of the available parameters:
            e.g. http://<yourdomain>/tweetledee/homerss.php?c=100&xrp=1
--------------------------------------------------------------------------------------------------*/
//debugging
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
$feedTitle = ' Twitter home timeline for ' . $twitterName;

// Defaults
$count = 25;  //default tweet number = 25
$exclude_replies = false;  //default to include replies
$screen_name = $data['screen_name'];

// Parameters
// c = tweet count ( possible range 1 - 200 tweets, else default = 25)
if (isset($_GET["c"])){
    $getcount = $_GET["c"];
    if ($getcount > 0 && $getcount <= 200){
        $count = $getcount;
    }
}

// xrp = exclude replies from the timeline (possible values: 1=true, else false)
if (isset($_GET["xrp"])){
    if ($_GET["xrp"] == 1){
        $exclude_replies = true;
    }
}

// request the user timeline using the paramaters that were parsed from URL or that are defaults
$code = $tmhOAuth->user_request(array(
			'url' => $tmhOAuth->url('1.1/statuses/home_timeline'),
			'params' => array(
          		'include_entities' => true,
    			'count' => $count,
    			'exclude_replies' => $exclude_replies,
        	)
        ));

// Anything except code 200 is a failure to get the information
if ($code <> 200) {
    echo $tmhOAuth->response['error'];
    die("home_timeline connection failure");
}

$homeTimelineObj = json_decode($tmhOAuth->response['response'], true);

//headers
header("Content-Type: application/rss+xml");
header("Content-type: text/xml; charset=utf-8");

// Start the output
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <atom:link href="<?= $my_domain ?><?= $_SERVER['PHP_SELF'] ?>" rel="self" type="application/rss+xml" />
        <lastBuildDate><?= date(DATE_RSS); ?></lastBuildDate>
        <language>en</language>
        <title><?= $feedTitle; ?></title>
        <description>
            Twitter home timeline updates for <?= $fullName; ?> / <?= $twitterName; ?>.
        </description>
        <link>http://www.twitter.com/<?= $twitterName; ?></link>
        <ttl>960</ttl>
        <generator>Tweetledee</generator>
        <category>Personal</category>
        <image>
        <title><?= $feedTitle; ?></title>
        <link>http://www.twitter.com/<?= $twitterName; ?></link>
        <url><?= $twitterAvatarUrl ?></url>
        </image>
        <?php foreach ($homeTimelineObj as $currentitem) : ?>
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
                <link>https://twitter.com/<?= $twitterName ?>/statuses/<?= $currentitem['id_str']; ?></link>
                <guid isPermaLink='false'><?= $currentitem['id_str']; ?></guid>

                <description>
                    <![CDATA[
                        <div style='float:left;margin: 0 6px 6px 0;'>
							<a href='https://twitter.com/<?= $twitterName ?>/statuses/<?= $currentitem['id_str']; ?>' border=0 target='blank'>
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
