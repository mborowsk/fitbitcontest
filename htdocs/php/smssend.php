<?php
require "TwilioAuth.php";
define("TO_NUM","+16107336135");

function curl_post($url, array $post = NULL, array $options = array())
{
	global $TwilioAccountSid;
	global $TwilioAuthToken;
    
    $defaults = array(
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_URL => $url,
        CURLOPT_FRESH_CONNECT => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FORBID_REUSE => 1,
        CURLOPT_TIMEOUT => 4,
		CURLOPT_USERPWD => $TwilioAccountSid.":".$TwilioAuthToken,
        CURLOPT_POSTFIELDS => http_build_query($post)
    );

    $ch = curl_init();
    curl_setopt_array($ch, ($options + $defaults));
    if( ! $result = curl_exec($ch))
    {
        trigger_error(curl_error($ch));
    }
    curl_close($ch);
    return $result;
} 

// Twilio REST API version.
$ApiVersion = "2008-08-01";

//Actual Twilio URL
$Turl = "https://api.twilio.com/2010-04-01/Accounts/".$TwilioAccountSid."/Messages";

$Parray = array(
            "To" => TO_NUM,
            "From" => FROM_NUM,
            "Body" => "Hello Fitbit Contest Participants");

curl_post($Turl, $Parray);


?>