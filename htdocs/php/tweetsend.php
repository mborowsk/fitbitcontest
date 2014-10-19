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

include_once "TwitterAPIExchange.php";
include_once "TwitterAuth.php";


//Initialize time zone
date_default_timezone_set ('America/New_York');

//Initialize variable with current date and time
$now = date('Y-m-d H:i:s');
//$time = $now->format('%h:%i:%s');

$url = 'https://api.twitter.com/1.1/statuses/update.json';
$tweetTxt = "Hello Fitbit Contest participants ".$now;

$postfields = array(
			'screen_name' => SCREEN_NAME, 
			'status' => $tweetTxt
			);
   
$requestMethod = 'POST';

$twitter = new TwitterAPIExchange($twitterSettings);
			$result=$twitter->buildOauth($url, $requestMethod)
				->setPostfields($postfields)
				->performRequest();

?>