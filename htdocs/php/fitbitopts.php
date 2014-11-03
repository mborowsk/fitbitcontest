<?php
# Check for AppFogs ENV variable
if( getenv("VCAP_SERVICES") ) {
    $json = getenv("VCAP_SERVICES");
} 
# Check for local file
else if( file_exists("./vcap.php") ) {
	echo "exists";
    $json = file_get_contents("./vcap.php");
} 
# No DB credentials
else {
    throw new Exception("No Database Information Available.", 1);
}
# Decode JSON and gather DB Info
$services_json = json_decode($json,true);

$fbit_config = $services_json["user-provided"][1]["credentials"];
$fbitKey = $fbit_config["FITBIT_CONSUMER_KEY"];
$fbitSecret = $fbit_config["FITBIT_CONSUMER_SECRET"];

define("FITBIT_OAUTH_HOST", "https://api.fitbit.com");
define("FITBIT_REQUEST_TOKEN_URL", FITBIT_OAUTH_HOST . "/oauth/request_token");
define("FITBIT_AUTHORIZE_URL", FITBIT_OAUTH_HOST . "/oauth/authorize");
define("FITBIT_ACCESS_TOKEN_URL", FITBIT_OAUTH_HOST . "/oauth/access_token");

define('OAUTH_TMP_DIR', function_exists('sys_get_temp_dir') ? sys_get_temp_dir() : realpath($_ENV["TMP"]));
?>
