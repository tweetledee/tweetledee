<?php
/***********************************************************************************************
 * Tweetledee  - Incredibly easy access to Twitter data
 *   userrss.php -- User timeline results formatted as a RSS feed
 *   Version: 0.2.9
 * Copyright 2013 Christopher Simpkins
 * MIT License
 ************************************************************************************************/
/*-----------------------------------------------------------------------------------------------
==> Instructions:
    - place the tweetledee directory in the public facing directory on your web server (frequently public_html)
    - Access the default user timeline feed (count = 25, includes both RT's & replies) at the following URL:
            e.g. http://<yourdomain>/tweetledee/userrss.php
==> User Timeline RSS feed parameters:
    - 'c' - specify a tweet count (range 1 - 200, default = 25)
            e.g. http://<yourdomain>/tweetledee/userrss.php?c=100
    - 'user' - specify the Twitter user whose timeline you would like to retrieve (default = account associated with access token)
            e.g. http://<yourdomain>/tweetledee/userrss.php?user=cooluser
    - 'xrt' - exclude retweets (1=true, default = false)
            e.g. http://<yourdomain>/tweetledee/userrss.php?xrt=1
    - 'xrp' - exclude replies (1=true, default = false)
            e.g. http://<yourdomain>/tweetledee/userrss.php?xrp=1
    - Example of all of the available parameters:
            e.g. http://<yourdomain>/tweetledee/userrss.php?c=100&xrt=1&xrp=1&user=cooluser
--------------------------------------------------------------------------------------------------*/
// debugging
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
$exclude_replies = false;  //default to include replies
$screen_name = $data['screen_name'];

// Parameters
// c = tweet count ( possible range 1 - 200 tweets, else default = 25)
if (isset($_GET["c"])){
    if ($_GET["c"] > 0 && $_GET["c"] <= 200){
        $count = $_GET["c"];
    }
}
// xrt = exclude retweets from the timeline ( possible values: 1=true, else false)
if (isset($_GET["xrt"])){
    if ($_GET["xrt"] == 1){
        $include_retweets = false;
    }
}
// xrp = exclude replies from the timeline (possible values: 1=true, else false)
if (isset($_GET["xrp"])){
    if ($_GET["xrp"] == 1){
        $exclude_replies = true;
    }
}
// user = Twitter screen name for the user timeline that the user is requesting (default = their own, possible values = any other Twitter user name)
if (isset($_GET["user"])){
    $screen_name = $_GET["user"];
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
// concatenate the URL for the atom href link
$thequery = $_SERVER['PHP_SELF'] .'?'. urlencode($_SERVER['QUERY_STRING']);

$userTimelineObj = json_decode($tmhOAuth->response['response'], true);

// Start the output
header("Content-Type: application/rss+xml");
header("Content-type: text/xml; charset=utf-8");
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <atom:link href="<?= $my_domain ?><?= $thequery ?>" rel="self" type="application/rss+xml" />
        <lastBuildDate><?= date(DATE_RSS); ?></lastBuildDate>
        <language>en</language>
        <title>Twitter user timeline feed for <?= $screen_name; ?></title>
        <description>
            Twitter user timeline updates for <?= $screen_name; ?>.
        </description>
        <link>http://www.twitter.com/<?= $screen_name; ?></link>
        <ttl>960</ttl>
        <generator>Tweetledee</generator>
        <category>Personal</category>
        <image>
        <title>Twitter user timeline updates for <?= $screen_name; ?></title>
        <link>http://www.twitter.com/<?= $screen_name; ?></link>
        <url><?= $twitterAvatarUrl ?></url>
        </image>
        <?php foreach ($userTimelineObj as $currentitem) : ?>
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
                <link>https://twitter.com/<?= $screen_name ?>/statuses/<?= $currentitem['id_str']; ?></link>
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
