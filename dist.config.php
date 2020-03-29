<?php

//You need to copy this file to config.php (without the dist) and
//set username, password and database-name and the api-key of your bot.

ini_set("display_errors", 1);
error_reporting(E_ALL & ~E_NOTICE);

$pdo = new PDO("mysql:dbname=arepo;host=localhost;charset=utf8", "username", "password");
$apikey = "myapi:key";
