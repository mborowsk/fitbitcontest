<?php

# Check for ENV variable
if( getenv("VCAP_SERVICES") ) {
    $json = getenv("VCAP_SERVICES");
} 
# Check for local file
else if( file_exists("./vcap.php") ) {
    $json = file_get_contents("./vcap.php");
} 
# No DB credentials
else {
    throw new Exception("No Database Information Available.", 1);
}
# Decode JSON and gather DB Info
$services_json = json_decode($json,true);

$twilio_creds = $services_json["user-provided"][0]["credentials"];
$TwilioAccountSid = $twilio_creds["accountSID"];
$TwilioAuthToken = $twilio_creds["authToken"];

?>