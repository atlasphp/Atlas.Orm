#!/usr/bin/env php
<?php
error_reporting(E_ALL);

$autoload = false;

$files = array(
    __DIR__ . '/../../../autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php'
);

foreach ($files as $file) {
    if (file_exists($file)) {
        $autoload = $file;
        break;
    }
}

if (! $autoload) {
    echo "Please install and update Composer before continuing." . PHP_EOL;
    exit(1);
}

require $autoload;

$cliFactory = new Aura\Cli\CliFactory();
$command = new Atlas\Command\Skeleton(
    $cliFactory->newContext($GLOBALS),
    $cliFactory->newStdio(),
    getcwd()
);
$code = $command();
exit($code);
