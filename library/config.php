<?php

$json = '{
"CTOOLS_DB_HOST" : "localhost",
"CTOOLS_DB_USER" : "root",
"CTOOLS_DB_PASS" : "",
"CTOOLS_DB_NAME" : "flowspot_db"
}';

$defaultConfig  = json_decode($json);
foreach($defaultConfig as $key=>$value){
    if(!getenv($key)){
        putenv($key.'='.$value);
    }
}


$host = getenv('CTOOLS_DB_HOST');
$user = getenv('CTOOLS_DB_USER');
$pass = getenv('CTOOLS_DB_PASS');
$name = getenv('CTOOLS_DB_NAME');

// DB_HOST - DB_USER - DB_PASSWORD - DB_NAME
$dbc = @mysqli_connect($host, $user, $pass, $name);

if (!$dbc) {
    http_response_code(500);
    die('Error connecting to ' . $host . ' ('. mysqli_connect_errno() .')  from ' . $_SERVER["SCRIPT_FILENAME"] . ' Error:'. mysqli_connect_error() . PHP_EOL);
    exit();
}

$dbc->set_charset('utf8');
