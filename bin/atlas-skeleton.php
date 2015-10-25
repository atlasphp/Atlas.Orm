<?php
use Aura\Cli\CliFactory;
use Atlas\Command\Skeleton;

// @todo make allowances for normal installations
require dirname(__DIR__) . "/vendor/autoload.php";

// create the command with its dependencies
$cliFactory = new CliFactory();
$command = new Skeleton(
    $cliFactory->newContext($GLOBALS),
    $cliFactory->newStdio(),
    getcwd()
);

// run the command and get its exit code
$exit = $command();

// done!
exit($exit);
