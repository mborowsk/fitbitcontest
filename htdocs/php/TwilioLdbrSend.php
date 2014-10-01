<?php
include_once "dbopts.php";
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

function twilioLdbrSend($message) {
global $TwilioAccountSid;
global $TwilioAuthToken;

global $dboptions;

$conn = mysql_connect($dboptions["server"] . ':' . $dboptions["port"], $dboptions["username"], $dboptions["password"]);

if(! $conn )
	{
		//echo $host. ':' . $dboptions["port"].$dboptions["username"].$dboptions["password"];
		die('Could not connect: ' . mysql_error());
	}
	
mysql_select_db($dboptions["database"]);

$sql_command = "SELECT * FROM SMS_TABLE";

$retval = mysql_query($sql_command, $conn);
	if(! $retval )
		{
		$progressReport = $progressReport . 'Database Delete Error: ' . mysql_error() . '.  Operation aborted...<br>';
		file_put_contents('SMSprog',$progressReport);
		die();
		}	

// Twilio REST API version.
$ApiVersion = "2008-08-01";

//Actual Twilio URL
$Turl = "https://api.twilio.com/2010-04-01/Accounts/".$TwilioAccountSid."/Messages";

while($record = mysql_fetch_array($retval)) {
	$Parray = array(
            "To" => $record['phone_num'],
            "From" => "541-233-4814",
            "Body" => $message);
	//curl_post($Turl, $Parray, $auth_array);
	curl_post($Turl, $Parray);
	}

mysql_close($conn);	
}

?>