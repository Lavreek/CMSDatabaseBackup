<?php

define('ROOT', __DIR__ .'/');

include ROOT ."verify.php";
require_once ROOT ."src/Database.php";

$ini = parse_ini_file(ROOT .".env", true);

$database = new Database($ini['MYSQL']);

$storagePath = ROOT ."storage/";

$tablesPath = $storagePath ."tables/";

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

if (isset($ini['ORDER_BY'])) {
    $database->setOrderBy($ini['ORDER_BY']);

} else {
    $database->setOrderBy([]);
}

$tables = $database->selectTablesName();

foreach ($tables as $table) {
    if (!is_dir($tablesPath . $table)) {
        mkdir($tablesPath . $table, recursive: true);
    }

    $tablePath = $tablesPath . $table . "/";

    $offset = 0;

    while ($objects = $database->selectTablePointer($table, offset: $offset)) {
        if (!is_null($objects)) {
            while ($object = mysqli_fetch_array($objects, MYSQLI_ASSOC)) {
                if (!isset($object['id'])) {
                    $filename = md5(serialize($object));

                } else {
                    $filename = $object['id'];
                }

                file_put_contents($tablePath . $filename .".obj", serialize($object));
            }
        }

        $offset++;
    }
}
