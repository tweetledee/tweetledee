<?php

interface Renderer
{

    public function render_feed($config, $tweets);

    public function using_cache($tldCache);

    public function using_client($mhOauth);
}

interface Twitter
{

    public function get_remote_content($tweet_url);
}

class Cache implements Twitter
{

    public function __construct($args)
    {
        $this->cache = $args;
    }

    public function get_remote_content($tweet_url)
    {
        if (preg_match("/.*twitter.com\/([^\/]+)\/status\/(.*)/", $tweet_url['expanded_url'], $out)) {
            // It is a tweet ! So download it now !
            $tweet = $this->cache->user_request(array(
                'url' => '1.1/statuses/show',
                'params' => array(
                    'id' => $out['2'],
                    'include_entities' => true
                )
            ));
            return $tweet;
        }
        return array();
    }
}

class Client implements Twitter
{

    public function __construct($args)
    {
        $this->client = $args;
    }

    public function get_remote_content($tweet_url)
    {
        if (preg_match("/.*twitter.com\/([^\/]+)\/status\/(.*)/", $tweet_url['expanded_url'], $out)) {
            // It is a tweet ! So download it now !
            $code = $this->client->user_request(array(
                'url' => $this->client->url('1.1/statuses/show'),
                'params' => array(
                    'id' => $out['2'],
                    'include_entities' => true
                )
            ));
            if ($code==200) {
                return json_decode($this->client->response['response'], true);
            } else {
                echo $this->client->response['error'];
                throw new Exception("unable to get content at ".$tweet_url);
            }
        }
        return array();
    }
}

abstract class AbstractRenderer implements Renderer
{

    public abstract function render_feed($config, $tweets);
    
    protected function create_parsed_tweet($tweet) {
        return tmhUtilities::entify_with_options(objectToArray($tweet), array(
            'target' => 'blank'
        ));
    }

    public function render_tweet($tweet)
    {
        return $this->render_parsed_tweet($tweet, $this->create_parsed_tweet($tweet));
    }

    abstract function render_parsed_tweet($currentitem, $parsed_tweet);

    public function using_cache($cache)
    {
        $this->client = new Cache($cache);
        return $this;
    }

    public function using_client($client)
    {
        $this->client = new Client($client);
        return $this;
    }
}

?>