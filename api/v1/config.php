<?php
define('DOCUMENT_ROOT', dirname(dirname(dirname(__FILE__))) . '/');
define('APPROOT', dirname(__FILE__));
define('API_ROOT', dirname(dirname(__FILE__)) . '/');
/*define('DATABASE_HOST', 'localhost');
define('DATABASE_USERNAME', 'root');
define('DATABASE_PASSWORD', '');
define('DATABASE_NAME', '4c');*/

define('DATABASE_HOST', 'localhost');
define('DATABASE_USERNAME', '4c-ims-dba');
define('DATABASE_PASSWORD', '@machmendeJAN2022.X');
define('DATABASE_NAME', '4c');

define('DATABASE_CHARSET', 'utf8mb4');
define('CURL_TIMEOUT', 30);
define('JWT_KEY', '4C-IMS-MasterDB');

define('SERVER_ERROR', "Oops, we encountered an error. Please try again. If this persists, don't hesitate to reach us.");
define('REPORT_ERROR', " could not be retrieved possibly due to missing data from the selected period.");
define('RENDER_ERROR', "Statement render failed. Please try again. If this persists, don't hesitate to reach us.");
define('SEMANTIC_ERROR', "Oops, we encountered an error. Please report this to our chat.");
define('CURL_ERROR', "Oops, we didn't anticipate this error. Kindly try again. Don't hesitate to reach us if this persists.");

header('Access-Control-Allow-Origin: *'); # Allow for Cross Origin Requests
header('Access-Control-Allow-Headers: *');
header('Access-Control-Allow-Methods: *');
date_default_timezone_set('Africa/Nairobi'); # Set the default timezone
error_reporting(E_ALL); # Enable error reporting for all errors. This should only be in development
ini_set('display_errors', 1); # Set the php config file to display errors
