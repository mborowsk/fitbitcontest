<?php

/**
 * fbit.php: Fitbit Oauth Token Verification using Google PHP Oauth Code
 *
 * @author Mark Borowski
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

//$store->install();
// Save the server in the the OAuthStore

$user_id = 1;

//$store->updateServer($options, $user_id);

//echo $store->listServers('',$user_id);

//var_dump($store->listServers('',$user_id));

try
{

	//  STEP 1:  If we do not have an OAuth token yet, go get one
	if (empty($_GET["oauth_token"]))
	{
		$getAuthTokenParams = array('scope' => 
			'https://api.fitbit.com',
			'xoauth_displayname' => 'Oauth test',
			'oauth_callback' => $callbackUrl);

		// get a request token
		$tokenResultParams = OAuthRequester::requestRequestToken(FITBIT_CONSUMER_KEY, $user_id, $getAuthTokenParams);

		//var_dump($tokenResultParams);

		//  redirect to the fitbit authorization page, they will redirect back
		//header("Location: " . FITBIT_AUTHORIZE_URL . "?btmpl=mobile&oauth_token=" . $tokenResultParams['token']);
		header("Location: " . FITBIT_AUTHORIZE_URL . "?oauth_token=" . $tokenResultParams['token']);
		exit;

		//echo ("Location: " . FITBIT_AUTHORIZE_URL . "?oauth_token=" . $tokenResultParams['token']);

	}
	else {
		//  STEP 2:  Get an access token
		$oauthToken = $_GET["oauth_token"];
		
		// echo "oauth_verifier = '" . $oauthVerifier . "'<br/>";
		$tokenResultParams = $_GET;
		
		try {
		    OAuthRequester::requestAccessToken(FITBIT_CONSUMER_KEY, $oauthToken, $user_id, 'POST', $_GET);
		}
		catch (OAuthException2 $e)
		{
			var_dump($e);
		    // Something wrong with the oauth_token.
		    // Could be:
		    // 1. Was already ok
		    // 2. We were not authorized
		    return;
		}


		$tokenResultParams = $_GET;
		// make the docs requestrequest.
		$request = new OAuthRequester("http://api.fitbit.com/1/user/-/friends.json", 'GET', $tokenResultParams);
		$result = $request->doRequest($user_id);

		if ($result['code'] == 200) {
			//var_dump($result['body']);
		}
		else {
			echo 'Error';
		}

        $jsResult = json_decode($result['body'],true);  

        //var_dump($jsResult);

        $friends = $jsResult["friends"];

        //var_dump($friends);

        $cnt=1;

        $friendsString = "";

        date_default_timezone_set('America/New_York');
        $today = date("Y-m-d");  
        //echo $today;

        $priv="";

        foreach ($friends as $key => $value) {

    		// make the docs requestrequest.
			$actRequest = new OAuthRequester("http://api.fitbit.com/1/user/".$value['user']['encodedId']."/activities/date/".$today.".json", 'GET', $tokenResultParams);

			try {
				$priv="";	
				$result = $actRequest->doRequest($user_id);	
			}
			catch (OAuthException2 $e)
			{
				//var_dump($e);
				$priv="*";		
		    //return;
			}

			if ($result['code'] == 200) {
				//var_dump($result['body']);
			}
			else {
				echo 'Error';
			}

        	$actResult = json_decode($result['body'],true);  

    		$friendsString .= $cnt++.". ".$value['user']['displayName']." -> ".$value['user']['encodedId']." ".$actResult['summary']['steps']." ".$priv."</br>";

        	//echo $actResult['summary']['steps'];
 
		}

        //echo $friendsString;

        //var_dump($store->listServerTokens($user_id));

        echo "oauth token success";

	}
}
catch(OAuthException2 $e) {
	echo "OAuthException:  " . $e->getMessage();
	var_dump($e);
}
?>