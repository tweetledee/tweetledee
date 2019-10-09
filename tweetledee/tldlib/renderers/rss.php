<?php
require 'renderer.php';

class RssRenderer extends AbstractRenderer
{

    public function render_parsed_tweet($currentitem, $parsedTweet)
    {
        $args = get_defined_vars();
        return template('tldlib/renderers/rss_item.php', $args);
    }

    public function render_feed($config, $tweets)
    {
        $args = array();
        $args['renderer'] = $this;
        $args['tweets'] = $tweets;
        $args = array_merge($args, $config, $tweets);
        return template('tldlib/renderers/rss_feed.php', $args);
    }
}
?>