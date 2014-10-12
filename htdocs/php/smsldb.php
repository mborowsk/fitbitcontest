<?php
include_once "dbopts.php";
require "TwilioAuth.php";
include('TwilioLdbrSend.php');

global $dboptions;

$response = ' there';

$conn = mysql_connect($dboptions["server"] . ':' . $dboptions["port"], $dboptions["username"], $dboptions["password"]);

if(! $conn )
	{
		//echo $host. ':' . $dboptions["port"].$dboptions["username"].$dboptions["password"];
		die('Could not connect: ' . mysql_error());
	}
	
mysql_select_db($dboptions["database"]);

$from_number = $_REQUEST['From'];	
$from_name = $_REQUEST['Body'];	
$lc_name = strtolower($from_name);

if ($lc_name == 'no') {
	//delete record to database
 	$sql_command = "DELETE FROM SMS_TABLE WHERE phone_num='$from_number'";

	$retval = mysql_query($sql_command, $conn);
	if(! $retval )
		{
		$progressReport = $progressReport . 'Database Delete Error: ' . mysql_error() . '.  Operation aborted...<br>';
		file_put_contents('SMSprog',$progressReport);
		die();
		}	
	$response = 'Thanks for trying Innovate Run Challenge.  You will no longer receive SMS messages until you resend your name to 541-233-4814';	
} else {
	//add record to database
	$sql_command = "INSERT INTO SMS_TABLE (name, phone_num) VALUES ('$from_name', '$from_number')";

	$retval = mysql_query($sql_command, $conn);
	if(! $retval )
		{
		$progressReport = $progressReport . 'Database Insert Error: ' . mysql_error() . '.  Operation aborted...<br>';
		file_put_contents('SMSprog',$progressReport);
		die();
		}	
	$response = $from_name.', Welcome to the Innovate Fit Challenge!';	
}
	
mysql_close($conn);	
	
// Twilio REST API version.
$ApiVersion = "2008-08-01";

//Actual Twilio URL
$Turl = "https://api.twilio.com/2010-04-01/Accounts/".$TwilioAccountSid."/Messages";

$Parray = array(
            "To" => $from_number,
            "From" => "XXX-XXX-XXXX",
            "Body" => $response);

curl_post($Turl, $Parray);

?>