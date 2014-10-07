
<?php
define("FITBIT_CONSUMER_KEY", "5598c70ef0df45e18961694c2b8a7f26"); // 
define("FITBIT_CONSUMER_SECRET", "1443152be07a43c9bd046f2f2ec8abb1"); // 

define("FITBIT_OAUTH_HOST", "https://api.fitbit.com");
define("FITBIT_REQUEST_TOKEN_URL", FITBIT_OAUTH_HOST . "/oauth/request_token");
define("FITBIT_AUTHORIZE_URL", FITBIT_OAUTH_HOST . "/oauth/authorize");
define("FITBIT_ACCESS_TOKEN_URL", FITBIT_OAUTH_HOST . "/oauth/access_token");

define('OAUTH_TMP_DIR', function_exists('sys_get_temp_dir') ? sys_get_temp_dir() : realpath($_ENV["TMP"]));
?>