<?php

/*******************************************************************
 *   Parameters
 *    - can pass via URL to web server
 *    - or as a short or long switch at the command line
 ********************************************************************/

/**
 * Possible parameters are defined here
 */
const NAME = "name";

const SHORT = "short";

const TYPE = "type";

const VALIDATION = "validation";

const MIN = "min";

const MAX = "max";

const LONG = "long";

const INT = "int";

const BOOL = "bool";

const PARAMETERS = array(
    "c" => array(
        NAME => "c",
        SHORT => "c",
        TYPE => INT,
        VALIDATION => array(
            MIN => 0,
            MAX => 200
        )
    ),
    "exclude_replies" => array(
        NAME => "exclude_replies",
        LONG => "xrp",
        TYPE => BOOL
    ),
    "cache_interval" => array(
        NAME => "cache_interval",
        LONG => "cache_interval",
        TYPE => INT
    )
);

function parameters_from($parameter_names)
{
    $parameters_objects = array();
    foreach ($parameter_names as $name) {
        if (array_key_exists($name, PARAMETERS)) {
            array_push($parameters_objects, PARAMETERS[$name]);
        }
    }
    return $parameters_objects;
}

function extract_value($type, $definition, $params)
{
    $extracted = $params[$definition[$type]];
    if ($definition[TYPE] == INT) {
        $value = intval($extracted);
        if (array_key_exists(VALIDATION, $definition)) {
            if ($value >= $definition[VALIDATION][MIN] && $value <= $definition[VALIDATION][MAX]) {
                return $value;
            } else {
                return null;
            }
        }
    } else if ($definition[TYPE] == BOOL) {
        $value = filter_var($extracted, FILTER_VALIDATE_BOOLEAN);
        return $value;
    } else {
        return $extracted;
    }
}

function load_parameters_from_command_line($parameters_definitions)
{
    $returned = array();
    if (isset($argv)) {
        $shortopts = "";
        $longopts = array();
        foreach ($parameters_definitions as $definition) {
            if (array_key_exists(SHORT, $definition)) {
                $shortopts = $shortopts . $definition[SHORT];
            }
            if (array_key_exists(LONG, $definition)) {
                array_push($longopts, $definition[LONG]);
            }
        }
        $params = getopt($shortopts, $longopts);
        foreach ($parameters_definitions as $definition) {
            if (array_key_exists(SHORT, $definition)) {
                if (array_key_exists($definition[SHORT], $params)) {
                    $returned[$definition[NAME]] = extract_value(SHORT, $definition, $params);
                }
            }
            if (array_key_exists(LONG, $definition)) {
                if (array_key_exists($definition[LONG], $params)) {
                    $returned[$definition[NAME]] = extract_value(LONG, $definition, $params);
                }
            }
        }
    }
    return $returned;
}

function load_parameters_from_http_request($parameters_definitions)
{
    $returned = array();
    foreach ($parameters_definitions as $definition) {
        if (array_key_exists(SHORT, $definition)) {
            if (array_key_exists($definition[SHORT], $_GET)) {
                $returned[$definition[NAME]] = extract_value(SHORT, $definition, $_GET);
            }
        }
        if (array_key_exists(LONG, $definition)) {
            if (array_key_exists($definition[LONG], $_GET)) {
                $returned[$definition[NAME]] = extract_value(LONG, $definition, $_GET);
            }
        }
    }
    return $returned;
}

/**
 * Load parameter from request or command line
 *
 * @param array $parameter_names
 *            values given here must be keys in PARAMETERS constant
 * @return array an array containg the parameters and their values as a dict
 */
function load_parameters($parameter_names)
{
    $parameters_definitions = parameters_from($parameter_names);
    // Command line parameter definitions //
    if (defined('STDIN')) {
        return load_parameters_from_command_line($parameters_definitions);
    } // end if
      // Web server URL parameter definitions //
    else {
        return load_parameters_from_http_request($parameters_definitions);
    } // end else
}
?>
