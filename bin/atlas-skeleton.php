<?php
use Aura\Cli\CliFactory;
use Aura\Cli\Status;

// @todo make allowances for normal installations
require dirname(__DIR__) . "/vendor/autoload.php";

// get the context and stdio objects
$cli_factory = new CliFactory();
$context = $cli_factory->newContext($GLOBALS);
$stdio = $cli_factory->newStdio();

// define options and named arguments through getopt
$options = [];
$getopt = $context->getopt($options);

// do we have a class to create?
$namespace = $getopt->get(1);
if (! $namespace) {
    $stdio->errln("Please provide a namespace to work in; e.g., Foo\\\\Bar\\\\Baz.");
    $stdio->errln("This command will create table and mapper classes in that namespace.");
    exit(Status::USAGE);
}

$dir = getcwd(). DIRECTORY_SEPARATOR;
$stdio->outln("Working in directory '$dir'.");

// repeat the last namespace name as the base class name
$lastNsPos = strrpos($namespace, '\\');
$baseClass = ltrim(substr($namespace, $lastNsPos), '\\');

//  create a subdirectory for the base class name
$subdir = $dir . $baseClass;
if (is_dir($subdir)) {
    $stdio->outln("Subdirectory for '$baseClass' exists.");
} else {
    $stdio->out("Creating subdirectory '$baseClass' ... ");
    $result = mkdir($subdir);
    if (! $result) {
        $stdio->outln('failed.');
        exit(Status::CANTCREAT);
    }
    $stdio->outln('done.');
}
$subdir .= DIRECTORY_SEPARATOR;

// skeletons for the classes
$typeUses = [
    'Table' => 'Atlas\Table\Table',
    'Row' => 'Atlas\Table\Row',
    'RowSet' => 'Atlas\Table\RowSet',
    'RowFilter' => 'Atlas\Table\RowFilter',
    'Mapper' => 'Atlas\Mapper\Mapper',
    'Record' => 'Atlas\Mapper\Record',
    'RecordSet' => 'Atlas\Mapper\RecordSet',
];

$tpl = <<<TPL
<?php
namespace {NAMESPACE};

use {USES};

class {CLASS} extends {TYPE}
{
}

TPL;

// create the classes
foreach ($typeUses as $type => $uses) {

    $class = $baseClass . $type;
    $file = $subdir . "{$class}.php";
    if (file_exists($file)) {
        $stdio->outln("SKIPPED: $file (already exists)");
        continue;
    }

    $data = [
        '{NAMESPACE}' => $namespace,
        '{USES}' => $uses,
        '{CLASS}' => $class,
        '{TYPE}' => $type,
    ];

    $code = strtr($tpl, $data);
    file_put_contents($file, $code);
    $stdio->outln("CREATED: $file");
}

$stdio->outln('Done!');
exit(Status::SUCCESS);
