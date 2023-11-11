<?php

// Place Codes/snippets at top of test file

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$prefix = "PHPFuse";
$dir = dirname(__FILE__) . "/../";

//require_once("{$dir}../_vendors/composer/vendor/autoload.php");

spl_autoload_register(function ($class) use ($dir, $prefix) {
    $classFilePath = null;
    $class = str_replace("\\", "/", $class);
    $exp = explode("/", $class);
    $sh1 = array_shift($exp);
    $path = implode("/", $exp) . ".php";
    if ($sh1 !== $prefix) {
        $path = "{$sh1}/{$path}";
    }

    $filePath = $dir . "../" . $path;
    require_once($filePath);
});
