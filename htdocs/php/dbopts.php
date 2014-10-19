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

$mysql_config = $services_json["cleardb"][0]["credentials"];
$db = $mysql_config["name"];
$host = $mysql_config["hostname"];
$port = $mysql_config["port"];
$username = $mysql_config["username"];
$password = $mysql_config["password"];

$dboptions = array('server' => $host, 'username' => $username,
                 'password' => $password,  'database' => $db, 'port' => $port);

//var_dump($dboptions);

?>