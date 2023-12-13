<?php

if (!php_sapi_name() == "cli") {
    die('Only CLI mode.');
}

$startFile = getopt('', ['env::']);

if (isset($startFile['env'])) {
    $ini = parse_ini_file(ROOT .$startFile['env'], true);


} else {
    $ini = parse_ini_file(ROOT .".env", true);
}
