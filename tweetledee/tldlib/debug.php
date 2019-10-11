<?php 
/*******************************************************************
 *  Debugging Flag (default = 0 = off)
 ********************************************************************/
$TLD_DEBUG = 0;
if ($TLD_DEBUG == 1){
    ini_set('display_errors', 'On');
    error_reporting(E_ALL | E_STRICT);
}

/*******************************************************************
 *  Client Side JavaScript Access Flag (default = 0 = off)
 ********************************************************************/
$TLD_JS = 0;
if ($TLD_JS == 1) {
    header('Access-Control-Allow-Origin: *');
}
?>