<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <atom:link href="<?php echo $atom ?>" rel="self" type="application/rss+xml" />
        <lastBuildDate><?php echo $lastBuildDate; ?></lastBuildDate>
        <language>en</language>
        <title><?php echo $title; ?></title>
        <description><?php echo $description; ?></description>
        <link><?php echo $link; ?></link>
        <?php if (isset($url)) { ?>
        <url><?php echo $url ?></url>
        <?php } ?>
        <ttl>960</ttl>
        <generator>Tweetledee</generator>
        <category>Personal</category>
        <image>
            <title><?php echo $title; ?></title>
            <link><?php echo $link; ?></link>
            <url><?php echo $twitterAvatarUrl ?></url>
        </image>
        <?php foreach ($tweets as $currentitem) : ?>
                 <?php echo $renderer->render_tweet($currentitem)?>
        <?php endforeach; ?>
    </channel>
</rss>
