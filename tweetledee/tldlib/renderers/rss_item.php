
<item>
 	<?php
if (isset($currentitem['retweeted_status'])) :
    $avatar = $currentitem['retweeted_status']['user']['profile_image_url'];
    $rt = '&nbsp;&nbsp;&nbsp;&nbsp;[<em style="font-size:smaller;">Retweeted by ' . $currentitem['user']['name'] . ' <a href=\'http://twitter.com/' . $currentitem['user']['screen_name'] . '\'>@' . $currentitem['user']['screen_name'] . '</a></em>]';
    $tweeter = $currentitem['retweeted_status']['user']['screen_name'];
    $fullname = $currentitem['retweeted_status']['user']['name'];
    $tweetTitle = $currentitem['retweeted_status']['full_text'];
else :
    $avatar = $currentitem['user']['profile_image_url'];
    $rt = '';
    $tweeter = $currentitem['user']['screen_name'];
    $fullname = $currentitem['user']['name'];
    $tweetTitle = $currentitem['full_text'];

endif;
?>
    <title>[<?php echo $tweeter; ?>] <?php echo $tweetTitle; ?> </title>
    <pubDate><?php echo reformatDate($currentitem['created_at']); ?></pubDate>
    <link>https://twitter.com/<?php echo $tweeter ?>/statuses/<?php echo $currentitem['id_str']; ?></link>
    <guid isPermaLink='false'><?php echo $currentitem['id_str']; ?></guid>
    <description>
<?php echo '<![CDATA['; ?>
<?php echo template('tldlib/renderers/rss_item_html_enclosure.php', array(
    'avatar' => $avatar,
    'rt' => $rt,
    'tweeter' => $tweeter,
    'fullname' => $fullname,
    'tweetTitle' => $tweetTitle,
    'currentitem' => $currentitem,
    'parsedTweet' => $parsedTweet))?>
<?php echo ']]'; ?>
    </description>
</item>
