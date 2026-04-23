<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

$secret_key = $_ENV['SECRET_KEY'];
$url_base = $_ENV['URL_BASE'];
$google_api_key = $_ENV['GOOGLE_API_KEY'];
$ID_OPAY = $_ENV['id_OPAY'];
$SK_OPAY = $_ENV['sk_OPAY'];
$PK_OPAY = $_ENV['pk_OPAY'];
$APPID_SQUARE = $_ENV['appId_square'];
$LOCID_SQUARE = $_ENV['locId_square'];
$TOKEN_SQUARE = $_ENV['accessToken_square'];

$host       = $_ENV['host'];
$db_name    = $_ENV['db_name'];
$username   = $_ENV['username'];
$password   = $_ENV['password'];

define('HOST', $host);
define('DB_NAME', $db_name);
define('USERNAME', $username);
define('PASSWORD', $password);


define('SECRET_KEY', $secret_key);
//define('URL_BASE', 'http://gaxybrincolines.com');
define('URL_BASE', $url_base);
// ... otras configuraciones de la aplicación
date_default_timezone_set('America/Mexico_City');

define('GOOGLE_API_KEY', $google_api_key);

define('id_OPAY', $ID_OPAY);
define('sk_OPAY', $SK_OPAY);
define('pk_OPAY', $PK_OPAY);

define('appId_square', $APPID_SQUARE);
define('locId_square', $LOCID_SQUARE);
define('accessToken_square',$TOKEN_SQUARE);
?>