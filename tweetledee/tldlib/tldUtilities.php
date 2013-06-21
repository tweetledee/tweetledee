<?php
/**
 * tldUtilities.php
 * -- Tweetledee utility functions
 * Copyright 2013 Christopher Simpkins
 * MIT License
 */

/* Acknowledgments: the following utility function source was included in this project and licensed under a MIT license
*      with the written permission of the original developer Geoff Smith.  Source link is documented where applicable
*/

/**------------------------------------------------------------------------------------------------*
 * Date reformatting
 * (Source: http://blog.fogcat.co.uk/2013/01/17/creating-an-rss-feed-for-your-twitter-home-page/)
 *-------------------------------------------------------------------------------------------------*/

function reformatDate($s) {
    $t = explode(' ', $s);
    return $t[0] . ', ' . $t[2] . ' ' . $t[1] . ' ' . $t[5] . ' ' . $t[3] . ' ' . $t[4];
}

/*-------------------------------------------------------------------------------------------------*
 * Parse tweet text turning links, users and hash tags into links
 * (Source: http://blog.fogcat.co.uk/2013/01/17/creating-an-rss-feed-for-your-twitter-home-page/)
 *-------------------------------------------------------------------------------------------------*/
 function parseTweet($s) {
    return parseTags(parseNames(parseLinks($s)));
}

/*-------------------------------------------------------------------------------------------------*
 * Parse URL's in the tweet text & turn them into links
 * (Source: http://blog.fogcat.co.uk/2013/01/17/creating-an-rss-feed-for-your-twitter-home-page/)
 *-------------------------------------------------------------------------------------------------*/
function parseLinks($s) {
    return preg_replace_callback(
                    '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', create_function(
                            '$matches', 'return "<a href=\'{$matches[0]}\'>{$matches[0]}</a>";'
                    ), $s
    );
}

/*-------------------------------------------------------------------------------------------------*
 * Parse Twitter user handles in the tweet text & turn them into links
 * (Source: http://blog.fogcat.co.uk/2013/01/17/creating-an-rss-feed-for-your-twitter-home-page/)
 *-------------------------------------------------------------------------------------------------*/
function parseNames($s) {
    return preg_replace('/@(\w+)/', '<a href="http://twitter.com/$1">@$1</a>', $s);
}

/*-------------------------------------------------------------------------------------------------*
 * Parse hash tags in the tweet text turning them into Twitter search links
 * (Source: http://blog.fogcat.co.uk/2013/01/17/creating-an-rss-feed-for-your-twitter-home-page/)
 *-------------------------------------------------------------------------------------------------*/
function parseTags($s) {
    return preg_replace('/\s+#(\w+)/', ' <a href="http://search.twitter.com/search?q=%23$1">#$1</a>', $s);
}


        function object_to_array(stdClass $Class){
            # Typecast to (array) automatically converts stdClass -> array.
            $Class = (array)$Class;

            # Iterate through the former properties looking for any stdClass properties.
            # Recursively apply (array).
            foreach($Class as $key => $value){
                if(is_object($value)&&get_class($value)==='stdClass'){
                    $Class[$key] = object_to_array($value);
                }
            }
            return $Class;
        }

/*-------------------------------------------------------------------------------------------------*
 * Create a PHP array from an object
 * (Source: http://blog.fogcat.co.uk/2013/01/17/creating-an-rss-feed-for-your-twitter-home-page/)
 *-------------------------------------------------------------------------------------------------*/
function objectToArray($object)
{
  if(!is_object( $object ) && !is_array( $object ))
  {
      return $object;
  }
  if(is_object($object) )
  {
      $object = get_object_vars( $object );
  }
  return array_map('objectToArray', $object );
}



?>