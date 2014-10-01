<?php

include('StravaAuth.php');

function StravaCallAPI($method, $url, $StravaAuth, $data = false)
{
    $curl = curl_init($url);

    switch ($method)
    {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
            //echo $url."<br/>";
            //echo $method."<br/>";
            //var_dump($StravaAuth);
            //echo "<br/>";
    }

    curl_setopt($curl,CURLOPT_HTTPHEADER, $StravaAuth);

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //echo 'curl exec in'."<br>";
	$results = curl_exec($curl);
    //echo 'curl exec out'."<br>";

	if ($results === FALSE) {
    	die(curl_error($curl));
	}
    return $results;
}

function StravaGetLeaderBoard($LDB)
{
    //echo $LDB."<br/>";
    global $StravaAuthBorowski;
    //var_dump($StravaAuthBorowski);
    //echo "<br/>";
    $results = StravaCallAPI('GET', 'https://www.strava.com/api/v3/segments/'.$LDB.'/leaderboard',$StravaAuthBorowski);
    return $results;
}

?>