<?php

/* Add PHP_VERSION_ID definition for PHP versions < 5.2.7*/
if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);

    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

/** pretty-prints json data, for human readability */
function json_pretty_print( $json, $html = false ) {
	$nl = $html ? "<br/>\n" : "\n";
	$tab = "  ";
	$new_json = "";
	$indent_level = 0;
	$in_string = false;
	$len = strlen($json);

	for($c = 0; $c < $len; $c++) {
		$char = $json[$c];
		switch($char) {
			case '{':
			case '[':
				if(!$in_string) {
					$new_json .= $char . $nl . str_repeat($tab, $indent_level+1);
					$indent_level++;
				} else {
					$new_json .= $char;
				}
				break;
			case '}':
			case ']':
				if(!$in_string) {
					$indent_level--;
					$new_json .= $nl . str_repeat($tab, $indent_level) . $char;
				} else {
					$new_json .= $char;
				}
				break;
			case ',':
				if(!$in_string) {
					$new_json .= "," . $nl . str_repeat($tab, $indent_level);
				} else {
					$new_json .= $char;
				}
				break;
			case ':':
				if(!$in_string) {
					$new_json .= ": ";
				} else {
					$new_json .= $char;
				}
				break;
			case '"':
				if($c > 0 && $json[$c-1] != '\\') {
					$in_string = !$in_string;
				}
			default:
				$new_json .= $char;
				break;
		}
	}

	return $new_json;
}

function json_encode_pretty_print( $data ) {
	if (PHP_VERSION_ID >= 50400) {
		return json_encode( $data, JSON_PRETTY_PRINT );
	} else {
		return json_pretty_print( json_encode( $data ), false );
	}
}

