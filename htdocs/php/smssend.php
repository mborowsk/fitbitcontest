<?php
require "TwilioAuth.php";

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

function curl_get($url)
{
	global $TwilioAccountSid;
	global $TwilioAuthToken;
    
    $defaults = array(
        CURLOPT_HEADER => 0,
        CURLOPT_URL => $url,
        CURLOPT_FRESH_CONNECT => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FORBID_REUSE => 1,
        CURLOPT_TIMEOUT => 4,
		CURLOPT_USERPWD => $TwilioAccountSid.":".$TwilioAuthToken
    );

    $ch = curl_init();
    curl_setopt_array($ch, $defaults);
    if( ! $result = curl_exec($ch))
    {
        trigger_error(curl_error($ch));
    }
    curl_close($ch);
    return $result;
} 

$to_number = $_GET['pn'];
 
// get phone number to send from
$Purl = "https://api.twilio.com/2010-04-01/Accounts/".$TwilioAccountSid."/IncomingPhoneNumbers.json";
$nums = curl_get($Purl);

//var_dump($nums);

$json_nums = json_decode($nums,true);  

//var_dump($json_nums);

$from_number= $json_nums["incoming_phone_numbers"][0]["phone_number"];

//Actual Twilio URL to post message
$Turl = "https://api.twilio.com/2010-04-01/Accounts/".$TwilioAccountSid."/Messages";

$Parray = array(
            "To" => $to_number,
            "From" => $from_number,
            "Body" => "Hello Fitbit Contest Participants");

curl_post($Turl, $Parray);


?>