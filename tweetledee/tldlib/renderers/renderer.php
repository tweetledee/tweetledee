<?php 
interface Renderer {
    public function render_feed($config, $tweets);
}

abstract class AbstractRenderer implements Renderer {
    
    public abstract function render_feed($config, $tweets);
    
    public function render_tweet($tweet) {
        $parsedTweet = tmhUtilities::entify_with_options(
            objectToArray($tweet),
            array(
                'target' => 'blank',
            )
            );
        return $this->render_parsed_tweet($tweet, $parsedTweet);
    }
    
    abstract function render_parsed_tweet($currentitem, $parsed_tweet);
}

?>