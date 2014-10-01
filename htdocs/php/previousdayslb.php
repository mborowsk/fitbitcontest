<?php

/**
 * oauth-php: Exampe OAuth client for accessing Google Docs
 *
 * @author BBG
 *
 * 
 * The MIT License
 * 
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

//Uncomment this line for the script to execute  be sure to hardcode $now and $today
//exit('The script did not execute...');

include_once "dbopts.php";
include_once "TwitterAPIExchange.php";
include_once "TwitterAuth.php";
include_once "TwilioLdbrSend.php";
include_once "./oauthphp/library/OAuthStore.php";
include_once "./oauthphp/library/OAuthRequester.php";

//Set default timezone to US Eastern
date_default_timezone_set ('America/New_York');

global $dboptions;
$db = $dboptions["database"];
$host = $dboptions["server"];
$port = $dboptions["port"];
$username = $dboptions["username"];
$password = $dboptions["password"];

$now = date('Y-m-d H:i:s');
//$now = '2014-05-30 23:59:59';

$yesterday = new DateTime(date('Y-m-d')); 
	$yesterday = date_sub($yesterday, new DateInterval('P1D'));
	$yesterday = $yesterday->format('Y-m-d');
	$today = $yesterday;
	$yesterday = $yesterday . ' 23:59:59';
	
	$store   = OAuthStore::instance('MySQL', $dboptions);

	//Initialize Progress Report
	$consoleLog = '';

	//Fetch the id of the current user
	$user_id = 1;


	// make the docs requestrequest.
	$request = new OAuthRequester("http://api.fitbit.com/1/user/-/friends.json", 'GET');		
	$result = $request->doRequest($user_id);

	if ($result['code'] == 200)
	{
		$consoleLog = $consoleLog . 'Retrieval of Friends List was Successful...<br>';
	}
	else
	{
		$consoleLog = $consoleLog . 'Retrieval of Friends List was Unsuccessful.  Operation aborted...<br>';
		//die();
	}

	$jsResult = json_decode($result['body'],true);  

	$friends = $jsResult["friends"];

	

	//set the current date for FitBit API call
	//$today = date("Y-m-d"); 
	//$today = '2014-05-30';

	$conn = mysql_connect($host . ':' . $port, $username, $password);
	if($conn)
	{
		$consoleLog = $consoleLog . 'Database connection was Successful...<br>';
	}
	else
	{
		$consoleLog = $consoleLog . 'Database connection was Unsuccessful.  Operation aborted...<br>';
	}

	if (mysql_select_db($db))
	{
		$consoleLog = $consoleLog . 'Successfully connected to database: ' . $db . '...<br>';
	}
	else
	{
		$consoleLog = $consoleLog . 'Unable to connect to the database: ' . $db . '.  Operation aborted...<br>';
	}

	//Build an array of all friend's latest activities
	$activitiesArray =array(array());

	foreach ($friends as $key => $value)
	{	
		//Make FitBit API call for each friend
		$actRequest = new OAuthRequester("http://api.fitbit.com/1/user/".$value['user']['encodedId']."/activities/date/".$today.".json", 'GET');

		//Get each friend's actvity details
		try
		{
			$actresult = $actRequest->doRequest($user_id);
		}
		catch (OAuthException2 $e)
		{
			$consoleLog = $consoleLog . 'Unable to retrieve Friends Activities.  This Friend will be ignored...<br>';
			continue;
		}
		
		$actResult = json_decode($actresult['body'],true);
		
		$activityRow = array($actResult['summary']['steps']=>array('steps'=>$actResult['summary']['steps'], 'activeminutes'=>$actResult['summary']['veryActiveMinutes'], 'fitbit_id'=>$value['user']['encodedId'],'display_name'=>$value['user']['displayName'], 'avatar'=>$value['user']['avatar']));
		$activitiesArray = array_merge($activitiesArray, $activityRow);	
	}
	
	//hack to remove first element which should never have been addded
	unset($activitiesArray[0]);
	
	//Sort array in descending order by steps, active minutes
	rsort($activitiesArray);
	
	$curRank = 1;
	foreach ($activitiesArray as $v1)
	{	
		//Insert new record into the database
		$sqlStmnt = 'INSERT INTO HISTORY (fitbit_id ,display_name, steps, active_minutes, avatar, update_datetime, ranking) VALUES ( "' . $v1['fitbit_id'] . '", "' . $v1['display_name'] . '", ' . $v1['steps'] . ', ' . $v1['activeminutes'] . ', "' . $v1['avatar'] . '", "' . $yesterday . '", ' . $curRank . ')';
		$sqlRet = mysql_query($sqlStmnt, $conn );
		if(! $sqlRet )
		{
			$consoleLog = $consoleLog . 'Unable to insert new record into the database...<br>';
		}
		
		$curRank++;
	}

	//Close the database connection
	mysql_close($conn);

	//Finalize log file and write to disk
	$consoleLog = $consoleLog . '<br>SUCCESS! at ' . $now . '<br><a href="http://www.innovatefit.com">View the InnovateFit Leader Board</a><br>';
	file_put_contents('consoleLog2.html', $consoleLog);
?>
