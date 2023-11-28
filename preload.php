<?php

define('ROOT', __DIR__ .'/');

include ROOT ."verify.php";
require_once ROOT ."src/Database.php";

$ini = parse_ini_file(ROOT .".env", true);

$database = new Database($ini['MYSQL']);

$options = getopt('', ['task::']);

if (isset($options['task'])) {
    switch ($options['task']) {
        case 'full' : {
            foreach ($ini['CUSTOM_REQUESTS'] as $request) {
                $database->executeQuery($request);
            }
            break;
        }
        default : {
            foreach ($ini['CUSTOM_REQUESTS'] as $task => $request) {
                if ($task == $options['task']) {
                    $database->executeQuery($request);
                }
            }
        }
    }
}
