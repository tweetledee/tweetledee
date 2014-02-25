<?php
/**
 * tldCache
 *
 * A cache for Tweetledee written in PHP.
 *
 * @author levymetal / Christian Varga
 * @version 0.1
 * Apache License
 *
 * 21 Feb 2014
 */
class tldCache {
  var $tmhOAuth;
  var $interval = 90; // 90 seconds

  /**
   * Creates a new tldCache object
   *
   * @param array $keys, the auth keys used to create a new tmhOAuth object
   * @return void
   */
  public function __construct( $keys=array(), $cache_interval = 90 ) {
    $this->tmhOAuth = new tmhOAuth( $keys );
    $this->interval = $cache_interval;
  }

  /**
   * Returns an tmhOAuth authorisation request object
   *
   * @return JSON object of thmOAuth->response['response']
   */
  public function auth_request() {
    // request the user information
    $url = $this->tmhOAuth->url( '1.1/account/verify_credentials' );
    $file = $this->sanitize( $url );

    if ( $cached_file = $this->get_cached_file( $file ) ) {
      return json_decode( $cached_file, true );
    }
    else {
      $code = $this->tmhOAuth->user_request(array(
                'url' => $url
              ));

      // If the request fails, check to see if there's an older cached file we can use
      if ( $code <> 200 ) {
        if ( $cached_file = $this->get_cached_file( $file, true ) ) {
          return json_decode( $cached_file, true );
        }
        else {
          if ( $code == 429 ) {
            die( "Exceeded Twitter API rate limit" );
          }

          die( "verify_credentials connection failure" );
        }
      }

      // Decode JSON
      $json = $this->tmhOAuth->response['response'];

      $this->set_cached_file( $file, $json );

      return json_decode( $json, true );
    }
  }

  /**
   * Returns an tmhOAuth request object
   *
   * @param $params the parameters for the request
   * @return JSON object of thmOAuth->response['response']
   */
  public function user_request( $params ) {
    $params['url'] = $this->tmhOAuth->url($params['url']);
    $file = $this->array_to_string( $params );

    if ( $cached_file = $this->get_cached_file( $file ) ) {
      return json_decode( $cached_file, true );
    }
    else {
      $code = $this->tmhOAuth->user_request( $params );

      // If the request fails, check to see if there's an older cached file we can use
      if ( $code <> 200 ) {
        if ( $cached_file = $this->get_cached_file( $file, true ) ) {
          return json_decode( $cached_file, true );
        }
        else {
          die( "user_timeline connection failure" );
        }
      }
      // concatenate the URL for the atom href link
      if ( defined( 'STDIN' ) ) {
        $thequery = $_SERVER['PHP_SELF'];
      } else {
        $thequery = $_SERVER['PHP_SELF'] .'?'. urlencode( $_SERVER['QUERY_STRING'] );
      }

      // Decode JSON
      $json = $this->tmhOAuth->response['response'];

      $this->set_cached_file( $file, $json );

      return json_decode( $json, true );
    }
  }

  /**
   * Converts a multidimensional array to a string
   *
   * @param $arr the array to be imploded
   * @return string of imploded array
   */
  private function array_to_string( $arr ) {
      $line = array();
      foreach( $arr as $v ) {
        $line[] = is_array( $v ) ? self::array_to_string( $v ) : $this->sanitize( $v );
      }
      return implode( $line );
  }

  /**
   * Function: sanitize
   * Returns a sanitized string, typically for URLs.
   * Source: http://stackoverflow.com/a/2668953/551093
   *
   * Parameters:
   *     $string - The string to sanitize.
   *     $force_lowercase - Force the string to lowercase?
   *     $anal - If set to *true*, will remove all non-alphanumeric characters.
   */
  private function sanitize($string, $force_lowercase = true, $anal = false) {
      $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
                     "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
                     "â€”", "â€“", ",", "<", ".", ">", "/", "?");
      $clean = trim(str_replace($strip, "", strip_tags($string)));
      $clean = preg_replace('/\s+/', "-", $clean);
      $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
      return ($force_lowercase) ?
          (function_exists('mb_strtolower')) ?
              mb_strtolower($clean, 'UTF-8') :
              strtolower($clean) :
          $clean;
  }

  /**
   * Checks a file to see if a timely cached version exists
   *
   * @param $file the name of the file to be checked
   * @param $ignore_interval false to use the interval, true to ignore the interval
   * @return cached file, or false on fail
   */
  private function get_cached_file( $file, $ignore_interval = false ) {
    $cache_file = dirname(__FILE__).'/cache/'.$file;
    $modified = @filemtime( $cache_file );
    $now = time();

    // check the cache file
    if ( ! $modified || ( ( $now - $modified ) > $this->interval ) && ! $ignore_interval ) {
      return false;
    }
    else {
      return file_get_contents( $cache_file );
    }
  }

  /**
   * Stores a cached file
   *
   * @param $file the name of the file to be saved
   * @param $json the data to be stored in the file
   * @return JSON object of thmOAuth->response['response']
   */
  private function set_cached_file( $file, $json ) {
    $cache_file = dirname(__FILE__).'/cache/'.$file;

    if ( $json && is_writable( dirname( $cache_file ) ) ) {
      $cache_static = fopen( $cache_file, 'w' );
      fwrite( $cache_static, $json );
      fclose( $cache_static );
    }
  }
}
