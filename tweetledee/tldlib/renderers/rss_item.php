
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
    if (isset($currentitem['entities']['media'][0]['media_url_https'])) :
        $picurl = $currentitem['entities']['media'][0]['media_url_https'];
        endif;

endif;
?>
    <title>[<?php echo $tweeter; ?>] <?php echo $tweetTitle; ?> </title>
    <pubDate><?php echo reformatDate($currentitem['created_at']); ?></pubDate>
    <link>https://twitter.com/<?php echo $tweeter ?>/statuses/<?php echo $currentitem['id_str']; ?></link>
    <guid isPermaLink='false'><?php echo $currentitem['id_str']; ?></guid>
    <description>
	<![CDATA[
                        <div style='float:left;margin: 0 6px 6px 0;'>
							<a href='https://twitter.com/<?php echo $tweeter ?>/statuses/<?php echo $currentitem['id_str']; ?>' border=0 target='blank'>
								<img src='<?php echo $avatar; ?>' border=0 />
							</a>
						</div>
                        <strong><?php echo $fullname; ?></strong> <a href='https://twitter.com/<?php echo $tweeter; ?>' target='blank'>@<?php echo $tweeter;?></a><?php echo $rt ?><br />
                        <?php echo $parsedTweet; ?>
                        <?php if(isset($currentitem['entities']['media'][0]['media_url_https'])): ?>
                        <img src='<?php echo $currentitem['entities']['media'][0]['media_url_https'] ?>' border=0 />
                        <?php endif; ?>
                    ]]>
	</description>
</item>
