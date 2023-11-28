<?php

define('ROOT', __DIR__ .'/');

include ROOT ."verify.php";
require_once ROOT ."src/Database.php";

$ini = parse_ini_file(ROOT .".env", true);

$database = new Database($ini['MYSQL']);

$options = getopt('', ['force::']);

if (isset($ini['NEEDED']['TABLES'])) {
    $database->setNeededTables($ini['NEEDED']['TABLES']);

} else {
    $database->setNeededTables("");
}

if (isset($ini['EXCEPTION']['TABLES'])) {
    $database->setExceptionTables($ini['EXCEPTION']['TABLES']);

} else {
    $database->setExceptionTables("");
}

if (isset($ini['UPDATE_ORDER'])) {
    $database->setUpdateOrder($ini['UPDATE_ORDER']);
}

$storagePath = ROOT ."storage/";

$tablesPath = $storagePath ."tables/";

$difference = ['.', '..'];

$tables = array_diff(scandir($tablesPath), $difference);

foreach ($tables as $table) {
    $objectsPath = $tablesPath . $table . "/";

    $objects = array_diff(scandir($objectsPath), $difference);

    while ($objects) {
        $objectFile = array_shift($objects);
        $objectSerialized = file_get_contents($objectsPath . $objectFile);
        $object = unserialize($objectSerialized);

        $productionObject = $database->getTableObject($table, $object);


        if ($productionObject) {
            $database->updateObject($table, $object);
        } else {
            $failedPath = $storagePath . "failed/$table/";

            if (!is_dir($failedPath)) {
                mkdir($failedPath, recursive: true);
            }

            copy($objectsPath . $objectFile, $failedPath . $objectFile);
        }
    }
}