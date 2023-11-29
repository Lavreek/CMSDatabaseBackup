<?php

define('ROOT', __DIR__ .'/');

include ROOT ."verify.php";
require_once ROOT ."src/Database.php";

$ini = parse_ini_file(ROOT .".env", true);

$failedTablesPath = ROOT . "storage/failed/";
$database = new Database($ini['MYSQL']);

$options = getopt('', ['task::']);
$difference = ['.', '..'];

$tables = array_diff(scandir($failedTablesPath), $difference);

foreach ($tables as $table) {
    $objects = array_diff(scandir($failedTablesPath . $table ."/"), $difference);

    foreach ($objects as $objectFile) {
        $objectPath = $failedTablesPath . $table ."/". $objectFile;
        $objectSerialized = file_get_contents($objectPath);
        $object = unserialize($objectSerialized);

        switch ($options['task']) {
            case 'insert' : {
                echo "Происходит операция вставки\n";

                $database->insertObject($table, $object);

                echo "Подготовка к удалению файла\n";
                unlink($objectPath);

                break;
            }
        }
    }
}
