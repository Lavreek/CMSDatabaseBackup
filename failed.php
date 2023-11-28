<?php

define('ROOT', __DIR__ .'/');

include ROOT ."verify.php";
require_once ROOT ."src/Database.php";

$ini = parse_ini_file(ROOT .".env", true);

$database = new Database($ini['MYSQL']);

$options = getopt('', ['task::']);
