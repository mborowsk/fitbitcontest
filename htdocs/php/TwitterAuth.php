<?php
//Twitter SetUp
#### Set access tokens ####
define("SCREEN_NAME", "fitbitcontest");

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

$twit_config = $services_json["user-provided"][2]["credentials"];
$twitToken = $twit_config["token"];
$twitTokenSecret = $twit_config["tokensecret"];
$twitKey = $twit_config["key"];
$twitSecret = $twit_config["secret"];

$twitterSettings = array(
        'oauth_access_token' => $twitToken,
        'oauth_access_token_secret' => $twitTokenSecret,
        'consumer_key' => $twitKey,
        'consumer_secret' => $twitSecret
    );

?>