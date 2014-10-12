
<?php
define("FITBIT_CONSUMER_KEY", "66f6ee05201842789ee45eeb1c826411"); // 
define("FITBIT_CONSUMER_SECRET", "1424d4bda97c4619948323419ce8d1ff"); // 

define("FITBIT_OAUTH_HOST", "https://api.fitbit.com");
define("FITBIT_REQUEST_TOKEN_URL", FITBIT_OAUTH_HOST . "/oauth/request_token");
define("FITBIT_AUTHORIZE_URL", FITBIT_OAUTH_HOST . "/oauth/authorize");
define("FITBIT_ACCESS_TOKEN_URL", FITBIT_OAUTH_HOST . "/oauth/access_token");

define('OAUTH_TMP_DIR', function_exists('sys_get_temp_dir') ? sys_get_temp_dir() : realpath($_ENV["TMP"]));
?>