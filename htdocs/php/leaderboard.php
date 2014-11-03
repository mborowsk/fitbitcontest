<?php

/**
 * leaderboard.php - logic that creates HTML leaderboard page
 *
 * @author Todd Long and Mark Borowski
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

include_once "dbopts.php";
include_once "TwitterAPIExchange.php";
include_once "TwitterAuth.php";
include_once "TwilioLdbrSend.php";
include_once "./oauthphp/library/OAuthStore.php";
include_once "./oauthphp/library/OAuthRequester.php";

//Set update interval in minutes
$updInterval = 2;

//Throttle back the number of API calls made. USED FOR TESTING PURPOSES ONLY
$throttle = 0;

//Initialize log file variable
$consoleLog = '';

//Initialize time zone
date_default_timezone_set ('America/New_York');

//Initialize variable with current date and time
$now = date('Y-m-d H:i:s');

//Establish connection to database server
$conn = mysql_connect($dboptions["server"] . ':' . $dboptions["port"], $dboptions["username"], $dboptions["password"]);
if(! $conn )
{
	$consoleLog = $consoleLog . 'An error ocurred while attempting to open a connection to the database instance.  Operation aborted with error: ' . mysql_error();
	echo $consoleLog;
	exit();
}
else
{
	$consoleLog = $consoleLog . 'A connection to the database instance has been established...<br>';
}

//Connect to database instance
$db = mysql_select_db($dboptions["database"]);
if(! $db )
{
	$consoleLog = $consoleLog . 'An error ocurred while attempting to connect to the database.  Operation aborted with error: ' . mysql_error();
	echo $consoleLog;
	exit();
}
else
{
	$consoleLog = $consoleLog . 'A connection to the database has been opened...<br>';
}


//Check to see if it is time to update the leaderboard
function okToUpdate()
{
	global $updInterval, $throttle, $numPreviousDays, $consoleLog, $now, $conn;
	
	$sqlStmnt = "SELECT MAX(update_datetime) AS update_datetime FROM HISTORY WHERE DATE(update_datetime) = SUBDATE(CURDATE(),1)";
    $sqlRetVal = mysql_query($sqlStmnt, $conn);
	$dbfield = mysql_fetch_assoc($sqlRetVal);
	$updDateTime = $dbfield['update_datetime'];
	
	if (substr($updDateTime, -8) <> '23:59:59'){
		include('previousdayslb.php');
	}
	
	//Get the key my_key indexed row from ACCESSCHECK table
	$sqlStmnt = "SELECT * FROM ACCESSCHECK WHERE id='my_key'";
    $sqlRetVal = mysql_query($sqlStmnt, $conn);
	$dbfield = mysql_fetch_assoc($sqlRetVal);
	$updInterval = $dbfield['update_interval'];
	$throttle = $dbfield['throttle'];
	$numPreviousDays = $dbfield['num_previous_days'];
	
	// subtract last Update_detetime from ACCESSCHECK my_key record from the current time
	$datetime1 = new DateTime($dbfield['update_datetime']);
	$datetime2 = new DateTime($now);
	$interval = $datetime1->diff($datetime2);
	$delta = $interval->format('%h:%i:%s');
	$deltaMin = $interval->format('%i');

	//if the number of minutes more then limit then store new time in update_datetime and return true=1 i.e. ok to update
	if ($deltaMin > $updInterval) {

		//update record in database
		$sqlStmnt = 'UPDATE ACCESSCHECK SET update_datetime = \''. $now. '\' WHERE id = \'my_key\' ';
		$sqlRetVal = mysql_query($sqlStmnt, $conn);
		
		if(! $sqlRetVal )
		{
			$consoleLog = $consoleLog . 'Could not update database with new ok to update date time.  Operation aborted with error: ' . mysql_error();
	  		echo $consoleLog;
	  		exit();
		}
		return 1;
	} 
	else
	{
		return 0;
	}	
}

//Update the leaderboard, if it is time to do so
if (okToUpdate() == 1) {
	
	$store   = OAuthStore::instance('MySQL', $dboptions);

	//Fetch the id of the current user
	$user_id = 1;


	// make the docs requestrequest.
	$request = new OAuthRequester("https://api.fitbit.com/1/user/-/friends.json", 'GET');		
	$result = $request->doRequest($user_id);

	if ($result['code'] == 200)
	{
		$consoleLog = $consoleLog . 'Retrieval of Friends List was Successful...<br>';
	}
	elseif ($result['code'] == 409)
	{
		$page = file_get_contents ('leaderboard.html'); 
		echo $page;
		$consoleLog = 'API limit exceeded.  Retry at the top of the next hour...';
		exit();
	}
	else
	{
		$consoleLog = $consoleLog . 'Retrieval of Friends List was Unsuccessful.  Operation aborted...<br>';
		echo $consoleLog;
		exit();
	}

	$jsResult = json_decode($result['body'],true);  
	$friends = $jsResult["friends"];

	//set the current date for FitBit API call
	$today = date("Y-m-d"); 

	//Build an array of all friend's latest activities
	$activitiesArray =array(array());
	
	$loopCtr = 1;
	
	foreach ($friends as $key => $value)
	{
		if($throttle < $loopCtr and $throttle <> 0)
		{
			continue;
		}
			
		//Make FitBit API call for each friend
		$actRequest = new OAuthRequester("https://api.fitbit.com/1/user/".$value['user']['encodedId']."/activities/date/".$today.".json", 'GET');
	
		//Get each friend's actvity details
		try
		{
			$actresult = $actRequest->doRequest($user_id);
			if ($actresult['code'] == 409)
			{
				$page = file_get_contents ('leaderboard.html'); 
				echo $page;
				$consoleLog = $consoleLog . 'API limit exceeded.  Retry at the top of the next hour...';
				exit();
			}
		}
		catch (OAuthException2 $e)
		{
			$consoleLog = $consoleLog . 'Unable to retrieve Friends Activities for ' . $value['user']['displayName'] .  '. This Friend will be ignored...<br>';
			continue;
		}
			
		$actResult = json_decode($actresult['body'],true);
			
		$activityRow = array($actResult['summary']['steps']=>array('steps'=>$actResult['summary']['steps'], 'activeminutes'=>$actResult['summary']['veryActiveMinutes'], 'fitbit_id'=>$value['user']['encodedId'],'display_name'=>$value['user']['displayName'], 'avatar'=>$value['user']['avatar']));
		$activitiesArray = array_merge($activitiesArray, $activityRow);	
		
		$loopCtr++;
	}
	
	//hack to remove first element which should never have been addded
	unset($activitiesArray[0]);
	
	//Sort array in descending order by steps, active minutes
	rsort($activitiesArray);

	//Declare array to be used to create barchart.json file containing name/steps as key/value pairs
	$barChartArray =array(array());
	
	//Get random number between 1 and 10, inclusive, and send tweet to participant with equal ranking
	$randRank = rand(1,10);
	
	//Initialize text to be passed to Twilio
	$twilioTxt = '';
	$twilioSnd = 0;
	
	//Initialize text to be posted to Twitter
	$tweetTxt = '';
	
	//Initialize current ranking counter
	$curRank = 1;
	
	foreach ($activitiesArray as $v1)
	{	
		//Get each friend's previous ranking
		$sqlStmnt = 'SELECT * FROM HISTORY AS a WHERE UPDATE_DATETIME = (SELECT MAX(UPDATE_DATETIME) FROM HISTORY AS b WHERE a.FITBIT_ID = b.FITBIT_ID AND b.FITBIT_ID = "' . $v1['fitbit_id'] . '")';
		$sqlRetVal = mysql_query($sqlStmnt, $conn);
		$prvRank = mysql_fetch_assoc($sqlRetVal);
		if(! $prvRank)
		{
			$consoleLog = $consoleLog . 'Unable to retrieve previous ranking from the database...<br>';
		}
		
		//Insert new record into the database
		$sqlStmnt = 'INSERT INTO HISTORY (fitbit_id ,display_name, steps, active_minutes, avatar, update_datetime, ranking) VALUES ( "' . $v1['fitbit_id'] . '", "' . $v1['display_name'] . '", ' . $v1['steps'] . ', ' . $v1['activeminutes'] . ', "' . $v1['avatar'] . '", "' . $now . '", ' . $curRank . ')';
		$sqlRetVal = mysql_query($sqlStmnt, $conn );
		if(! $sqlRetVal )
		{
			$consoleLog = $consoleLog . 'Unable to insert new record into the database...<br>';
		}
		
		//Determine change, if any, between previous and current rankings
		$lbChange = intval($prvRank['ranking']) - $curRank;
		
		//Build the top five list to send to Twilio.  We'll decide later whether or not there are any changes to the top five
		if( $curRank < 6)
		{
			$twilioTxt = $twilioTxt . '#' . $curRank . ' ' . $v1['display_name'] . ' (' . ($v1['steps']) . ') ' . $lbChange . "\n";
		}
		
		//If previous and current rankings are equal
		if ($lbChange == 0)
		{
			$posChange = "<center>No<br>Change</center>";
			$tweetTxt = 'TopTen@' . date('H:i') . ' Congrats ' . $v1['display_name'] . ', you\'ve maintained your #' . $curRank . ' position on the @fitbitcontest Top Ten leaderboard with ' . $v1['steps'] . ' steps.';
		}
		//If previous ranking is lower than current ranking
		elseif ($lbChange > 0)
		{
			$posChange = "<center>Up by<br>" . $lbChange . "</center>";
			
			//If friend has moved to number one position, then force this tweet
			if ($curRank == 1)
			{
				$tweetTxt = 'TopTen@' . date('H:i') . ' Way to Go ' . $v1['display_name'] . '!!! You\'ve reached the top of the @fitbitcontest leaderboard at #' . $curRank . ' with ' . $v1['steps'] . ' steps!';
				$randRank = $curRank;
			}
			else
			{
				$tweetTxt = 'TopTen@' . date('H:i') . ' Congrats ' . $v1['display_name'] . ', you\'ve moved up on the @fitbitcontest Top Ten leaderboard and are now ranked #' . $curRank . ' with ' . $v1['steps'] . ' steps.';		
			}
			
			//If there is a change in the top five, flag the Twilio SMS
			if($curRank < 6)
			{
				$twilioSnd = 1;
			}
		}
		//If previous ranking is higher than current ranking
		elseif ($lbChange < 0)
		{
			$lbChange = $lbChange * -1;
			$posChange = "<center>Down by<br>" . $lbChange . "</center>";
			$tweetTxt = 'TopTen@' . date('H:i') . ' Warning ' . $v1['display_name'] . ', you\'re still on the @fitbitcontest Top Ten leaderboard, but you\'ve slipped from #' . $prvRank['ranking'] . ' to #' . $curRank . ' with ' . $v1['steps'] . ' steps.';

			//If there is a change in the top five, flag the Twilio SMS			
			if($curRank < 6)
			{
				$twilioSnd = 1;
			}
		}

		//If current ranking is within the top ten, then post to Twitter
		if ($curRank == $randRank and $v1['steps'] > 0)
		{
			$url = 'https://api.twitter.com/1.1/statuses/update.json';
			$tweetTxt = chunk_split($tweetTxt, 140);

			$postfields = array(
			'screen_name' => SCREEN_NAME, 
			'status' => $tweetTxt
			);
    
			$requestMethod = 'POST';

			$twitter = new TwitterAPIExchange($twitterSettings);
			$result=$twitter->buildOauth($url, $requestMethod)
				->setPostfields($postfields)
				->performRequest();
		}

		
		//Append this friend's results to the dynamically built HTML file
		$dynHTML = $dynHTML . '<tr><td style="text-align: right">' . '#' . $curRank . '</td> <td><img src="'. $v1['avatar'] . '" height="48" width="50"></td> <td>' . $v1['display_name'] . '</td> <td style="text-align: right">' . (string)$v1['steps'] . '</td> <td style="text-align: right">' . $v1['activeminutes'] . '</td><td style="text-align: right">' . '#' . $prvRank['ranking'] . '</td><td style="text-align: right">' . $posChange . '</td></tr>';
		
		//Collect this friend's results for display on the performance graph
		$new_row = array($v1['display_name']=>array('steps'=>$v1['steps'],'active_minutes'=>$v1['activeminutes']));
		$barChartArray = array_merge($barChartArray, $new_row);	
		
		//Increment current ranking counter
		$curRank++;
	}

	$loopCtr = 1;
	while($loopCtr <= $numPreviousDays){ 

		$previousDay = new DateTime(date('Y-m-d')); 
		$previousDay = date_sub($previousDay, new DateInterval('P' . $loopCtr . 'D'));	
		$previousDay = $previousDay->format('Y-m-d');
		$previousDay = $previousDay . ' 23:59:59';
		
		$sqlStmnt = "SELECT * FROM HISTORY WHERE UPDATE_DATETIME = '" . $previousDay . "' ORDER BY RANKING LIMIT 0, 5";
		$sqlRet = mysql_query($sqlStmnt, $conn);
		
		$displayDate = new DateTime(date('l F jS'));
		$displayDate = date_sub($displayDate, new DateInterval('P' . $loopCtr . 'D'));	
		$displayDate = $displayDate->format('l F jS');
		
		$previousDayTopFive = '';
		while ($dbfield = mysql_fetch_assoc($sqlRet))
		{
			$previousDayTopFive = $previousDayTopFive . '<tr><td style="text-align: left" width="100">' . $dbfield['display_name'] . '</td><td style="text-align: right" width="60">' . $dbfield['steps'] . '</td><td style="text-align: right" width="50">' . $dbfield['active_minutes'] . '</td></tr>';
		}
		$previousDaysTopFive = $previousDaysTopFive . '<table border="1" style="position: relative"><caption align="top">' . $displayDate . '</caption><tbody>' . $previousDayTopFive . '</tbody></table><br />';
		
		$loopCtr++;
	}

	//Finalize the HTML content
	$dynHTML = '<!DOCTYPE html> <html> <head> <meta charset="US-ASCII"> <title>InnovateFit 2014 Leader Board</title> </head> <body><table><tbody><tr><td><table border="1"><tbody><br>InnovateFit Leaderboard as of: ' . date('l h:i:s A') . '<br>Next update in approximately ' . $updInterval . ' minutes...<tr><td style="color: white; background-color: #06b1da; text-align: center" width="60">Current<br>Ranking</td><td style="color: white; background-color: #8ec641; text-align: center" width="50"><br>Avatar</td><td style="color: white; background-color: #02416b; text-align: center" width="150"><br>Name</td><td style="color: white; background-color: #84d1f5; text-align: center" width="60"><br>Steps</td><td style="color: white; background-color: #2c9d70; text-align: center" width="60">Active<br>Minutes</td><td style="color: white; background-color: #06b1da; text-align: center" width="60">Previous<br>Ranking</td><td style="color: white; background-color: #8ec641; text-align: center" width="60">Position<br>Change</td></tr>' . $dynHTML . '</tbody> </table></td><td>'  . $previousDaysTopFive . '</td></body> </html>';


	//Wtire HTML content to disk
	file_put_contents('leaderboard.html', $dynHTML);
	
	//Echo the dynamically built HTML content
	echo $dynHTML;
	
	//Write friend's name, steps and active minutes to a file, which will be consumed by the google graph
	unset($barChartArray[0]);
	file_put_contents("barchart.json",json_encode($barChartArray));

	//Finalize log file and write to disk
	$consoleLog = $consoleLog . '<br>SUCCESS! at ' . $now . 'View the Leader Board</a><br>';

	file_put_contents('consoleLog.html', $consoleLog);
	mysql_close($conn);
	
	//Finalize text to be passed to Twilio and send SMS
	if($twilioSnd == 1)
	{
		$twilioTxt = "Fitbit Contest TopFive" . "\n" . "as of " . date('H:i') . "\n" . $twilioTxt;
		twilioLdbrSend($twilioTxt); 
	}
}

//If it is not time to update, then simply display the latest leaderboard
else
{
	$page = file_get_contents ('leaderboard.html'); 
	echo $page;
}

/*
function __destruct()
{
	global $consoleLog, $conn;
	
	file_put_contents('consoleLog.html', $consoleLog);
	
	if($conn)
	{
		mysql_close($conn);		
	}
}
*/
?>
