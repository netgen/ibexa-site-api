<?php

declare(strict_types=1);

$file = __DIR__ . '/../vendor/autoload.php';
if (!\file_exists($file)) {
    throw new RuntimeException('Install dependencies using composer to run the test suite.');
}

\date_default_timezone_set('Europe/Zagreb');

$autoload = require_once $file;
