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

if (isset($ini['USE_COLUMN'])) {
    $database->setUsingColumns($ini['USE_COLUMN']);

} else {
    $database->setUsingColumns([]);
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
                    $object['id'] = md5(serialize($object));
                }

                file_put_contents($tablePath . $object['id'] .".obj", serialize($object));
            }
        }

        $offset++;
    }
}
