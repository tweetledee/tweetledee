<?php 
interface Renderer {
    public function render_tweet($twitterName, $tweet);
}

abstract class AbstractRenderer implements Renderer {
    
    public abstract function render_feed($config, $tweets);
    
    public function render_tweet($twitterName, $tweet) {
        $parsedTweet = tmhUtilities::entify_with_options(
            objectToArray($tweet),
            array(
                'target' => 'blank',
            )
            );
        return $this->render_parsed_tweet($twitterName, $tweet, $parsedTweet);
    }
    
    abstract function render_parsed_tweet($twitterName, $currentitem, $parsed_tweet);
}

?>