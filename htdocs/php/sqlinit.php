<?php
include_once "dbopts.php";
include_once "fitbitopts.php";
include_once "./oauthphp/library/OAuthStore.php";
include_once "./oauthphp/library/OAuthRequester.php";

//  Init the OAuthStore
$options = array(
	'consumer_key' => FITBIT_CONSUMER_KEY, 
	'consumer_secret' => FITBIT_CONSUMER_SECRET,
	'server_uri' => FITBIT_OAUTH_HOST,
	'signature_methods' => array('HMAC-SHA1', 'PLAINTEXT'),
	'request_token_uri' => FITBIT_REQUEST_TOKEN_URL,
	'authorize_uri' => FITBIT_AUTHORIZE_URL,
	'access_token_uri' => FITBIT_ACCESS_TOKEN_URL
);

//$callbackUrl
$callbackUrl = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
//echo $callbackUrl;

// Note: do not use "Session" storage in production. Prefer a database
// storage, such as MySQL.

$store   = OAuthStore::instance('MySQL', $dboptions);


$store->install();
// Save the server in the the OAuthStore

$user_id = 1;

$store->updateServer($options, $user_id);

//echo $store->listServers('',$user_id);

//var_dump($store->listServers('',$user_id));

function openConnection(){
	//parse vcap_services
/*	$services = getenv("VCAP_SERVICES");
	$services_json = json_decode($services,true);
	$mysql_config = $services_json["mysql-5.5"][0]["credentials"];
	$db = $mysql_config["name"];
	$host = $mysql_config["host"];
	$port = $mysql_config["port"];
	$username = $mysql_config["user"];
	$password = $mysql_config["password"];
	$conn = mysql_connect($host . ':' . $port, $username, $password);
*/
	global $dboptions;

	$conn = mysql_connect($dboptions["server"] . ':' . $dboptions["port"], $dboptions["username"], $dboptions["password"]);


	if(! $conn )
	{
			//echo $host. ':' . $dboptions["port"].$dboptions["username"].$dboptions["password"];
		die('Could not connect: ' . mysql_error());
	}
	
	mysql_select_db($dboptions["database"]);
	
	return $conn;
}

function closeConnection($conn){
	mysql_close($conn);	
}

function sqlCreateSMSTable(){
	
	$conn = openConnection();

	$sql_create = 'CREATE TABLE SMS_TABLE(name CHAR(32), phone_num CHAR(12))';

	$retval = mysql_query($sql_create, $conn );
	if(! $retval )
	{
  	echo('Could not create database table: ' . mysql_error() . '<br/>');
	} else {
  	echo "Created SMS database table successfully<br/>";
	}
/*
	//add record to database
	$sql_insert = 'INSERT INTO ACCESSCHECK (id ,check_sum, ok2access, update_datetime) VALUES ( \'my_key\',\'DEADBEEFDEADBEEFDEADBEEFDEADBEEF\', \'1\', "'. $now . '")';

	$retval = mysql_query($sql_insert, $conn );
	if(! $retval )
	{
		$progressReport = $progressReport . 'Database Insert Error: ' . mysql_error() . '.  Operation aborted...<br>';
		echoProgress($progressReport);
		die();
	}
*/	
	closeConnection($conn);
}
function sqlCreateCheckTimeTable(){
	
	$conn = openConnection();

	//Set default timezone to US Eastern
    date_default_timezone_set ('America/New_York');

    $now = date('Y-m-d H:i:s');

	$sql_create = 'CREATE TABLE ACCESSCHECK(id CHAR(6) default "my_key", check_sum CHAR(32), ok2access INT default 1, update_datetime DATETIME, update_interval INT default 20, throttle INT default 0)';

	$retval = mysql_query($sql_create, $conn );
	if(! $retval )
	{
  	echo('Could not create database table: ' . mysql_error() . '<br/>');
	} else {
  	echo "Created Timetable database table successfully<br/>";
	}

	//add record to database
	$sql_insert = 'INSERT INTO ACCESSCHECK (id ,check_sum, ok2access, update_datetime) VALUES ( \'my_key\',\'DEADBEEFDEADBEEFDEADBEEFDEADBEEF\', \'1\', "'. $now . '")';

	$retval = mysql_query($sql_insert, $conn );
	if(! $retval )
	{
		$progressReport = $progressReport . 'Database Insert Error: ' . mysql_error() . '.  Operation aborted...<br>';
		echoProgress($progressReport);
		die();
	}
	
	closeConnection($conn);
}

function sqlCreateTable(){
	
	$conn = openConnection();

	$sql_create = 'CREATE TABLE HISTORY(fitbit_id CHAR(6), display_name CHAR(30), steps BIGINT, active_minutes INT, avatar CHAR(255), update_datetime DATETIME DEFAULT "2014-01-01 00:00:00", ranking INT(11), PRIMARY KEY (fitbit_id,update_datetime),
  KEY updatedt_idx (update_datetime))';

	$retval = mysql_query($sql_create, $conn );
	if(! $retval )
	{
  	echo('Could not create database table: ' . mysql_error() . '<br/>');
	} else {
  	echo "Created History database table successfully<br/>";
	}
	
	closeConnection($conn);
}

function sqlDropTable(){
	$conn = openConnection();
	
	$sql_drop = 'DROP TABLE HISTORY';

	$retval = mysql_query($sql_drop, $conn);

	if(! $retval )
	{
  	die('Could not drop FACTS table: ' . mysql_error());
	}
	echo "Dropped table successfully";
	
	closeConnection($conn);
}

function sqlSelectAllTables()
{
	$conn = openConnection();

	$sqlStmnt = 'select table_name, table_catalog, table_schema, create_time from information_schema.tables where table_schema = "dc3e4722fce0a4c6c8962bb3a0857c2f3" order by create_time desc';

	$retval = mysql_query($sqlStmnt, $conn );
	if(! $retval )
	{
  		echo('Could not retrieve database tables: ' . mysql_error() . '<br/>');
	}
	
	while ($dbfield = mysql_fetch_assoc($retval))
	{
    	echo $dbfield['table_name'] . ' ' . $dbfield['table_catalog'] . ' ' . $dbfield['table_schema'] . ' ' . $dbfield['create_time'] . '<br/>';
	}
	
	closeConnection($conn);
}

function sqlInsert($fitbit_id, $display_name, $steps, $active_minutes, $avatar){
	$conn = openConnection();
	
	$sql_insert = 'INSERT INTO HISTORY (fitbit_id ,display_name, steps, active_minutes, avatar, update_datetime) VALUES ( "' . $fitbit_id . '", "' . $display_name . '", ' . $steps . ', ' . $active_minutes . ', "' . $avatar . '", NOW() )';

	$retval = mysql_query($sql_insert, $conn );
	if(! $retval )
	{
	  	die('Could not enter data: ' . mysql_error());
	}
	
	closeConnection($conn);
}

function sqlRead(){
	
	$conn = openConnection();
	
	$sql_read = "SELECT * FROM HISTORY WHERE UPDATE_DATETIME = '2014-05-05 23:59:59' ORDER BY UPDATE_DATETIME DESC ";
	//$sql_read = "DELETE FROM HISTORY WHERE UPDATE_DATETIME = '2014-05-05 23:59:59'";

	$retval = mysql_query($sql_read, $conn );
	if(! $retval )
	{
	  	die('Could not read from table: ' . mysql_error());
	}
	while ($dbfield = mysql_fetch_assoc($retval)) {
    	echo $dbfield['fitbit_id'] . '	' . $dbfield['display_name'] . ' ' . $dbfield['steps'] . ' ' . $dbfield['active_minutes'] . ' ' . $dbfield['avatar'] . ' ' . $dbfield['update_datetime'] . ' ' . $dbfield['update_datetime'] . '<br/>';
	}
	
	closeConnection($conn);
}


function okToUpdate(){

	global $dboptions;

	$conn = mysql_connect($dboptions["server"] . ':' . $dboptions["port"], $dboptions["username"], $dboptions["password"]);


	if(! $conn )
	{
		die('Could not connect: ' . mysql_error());
	}
	
	mysql_select_db($dboptions["database"]);
	
	//Get the key my_key indexed row from ACCESSCHECK table
	$sqlStmnt = "SELECT * FROM ACCESSCHECK WHERE id='my_key'";

    $sqlRetVal = mysql_query($sqlStmnt, $conn);

	$dbfield = mysql_fetch_assoc($sqlRetVal);

	//echo $dbfield['ok2access'];
	//echo "<br/>";
	//echo $dbfield['update_datetime'];
	//echo "<br/>";
	var_dump($dbfield);

	date_default_timezone_set ('America/New_York');
    $now = date('Y-m-d H:i:s');

	echo "<br/>";    
	echo $now;
	echo "<br/>";    

	// subtract last Update_detetime from ACCESSCHECK my_key record from the current time
	$datetime1 = new DateTime($dbfield['update_datetime']);
	$datetime2 = new DateTime($now);
	$interval = $datetime1->diff($datetime2);
	$delta = $interval->format('%h:%i:%s');
	$deltaMin = $interval->format('%i');

	echo "<br/>";    
	echo $delta;
	echo "<br/>";
	echo $deltaMin;

	//if the number of minutes more then limit the store new time in update_datetime and return true=1
	if ($deltaMin > 1) {
		echo "<br/>".'greater';
		//update record in database
		$sql_update = 'UPDATE ACCESSCHECK SET update_datetime = \''. $now. '\' WHERE id = \'my_key\' ';

		echo "<br/>";
		echo $sql_update;

		$retval = mysql_query($sql_update, $conn);
		if(! $retval )
			{
	  		die('Could not update data: ' . mysql_error());
		}
		closeConnection($conn);
		return 1;
	} 
	else {
		echo "<br/>".'NOTgreater';
		closeConnection($conn);
		return 0;
	}	
}

//$ok = okToUpdate();
//echo $ok;
//sqlDropTable();
//openConnection();
sqlCreateTable();
sqlCreateSMSTable();
sqlCreateCheckTimeTable();
//sqlSelectAllTables();
//sqlRead();
?>