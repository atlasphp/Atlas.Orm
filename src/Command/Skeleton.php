<?php
namespace Atlas\Command;

use Aura\Cli\Context;
use Aura\Cli\Status;
use Aura\Cli\Stdio;

class Skeleton
{
    protected $context;
    protected $stdio;
    protected $getopt;
    protected $namespace;
    protected $dir;
    protected $subdir;
    protected $type;
    protected $classes = [
        'Table',
        'Row',
        'RowIdentity',
        'RowSet',
        'RowFilter',
        'Mapper',
        'Record',
        'RecordSet',
    ];

    public function __construct(Context $context, Stdio $stdio)
    {
        $this->context= $context;
        $this->stdio = $stdio;
        $this->dir = getcwd();
    }

    protected function setGetopt()
    {
        // define options and named arguments through getopt
        $options = [
            'noautoinc',
            'dir:',
            'primary:',
            'table:',
            'cols:',
        ];
        $this->getopt = $this->context->getopt($options);

        if (! $this->getopt->hasErrors()) {
            return;
        }

        $errors = $this->getopt->getErrors();
        foreach ($errors as $error) {
            $this->stdio->errln($error->getMessage());
        }
        return STATUS::USAGE;
    }

    protected function setNamespace()
    {
        $this->namespace = $this->getopt->get(1);
        if (! $this->namespace) {
            $this->stdio->errln('Please provide a namespace to work in; e.g., Foo\\\\Bar\\\\Baz.');
            $this->stdio->errln('This command will create table and mapper classes in that namespace.');
            return Status::USAGE;
        }

        // repeat the last namespace name as the row-type name
        $lastNsPos = (int) strrpos($this->namespace, '\\');
        $this->type = ltrim(substr($this->namespace, $lastNsPos), '\\');
    }

    protected function setDir()
    {
        $dir = $this->getopt->get('--dir');
        if ($dir) {
            $this->dir = $dir;
        }
        $this->dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->stdio->outln("Working in directory '$dir'.");
        return $this->setSubDir();
    }

    protected function setSubDir()
    {
        //  create a subdirectory for the type name
        $this->subdir = $this->dir . $this->type . DIRECTORY_SEPARATOR;
        if (is_dir($this->subdir)) {
            $this->stdio->outln("SKIPPED: {$this->subdir} (already exists)");
            return;
        }

        $result = @mkdir($this->subdir, 0755, true);
        if (! $result) {
            $this->stdio->outln("FAILED!: {$this->subdir} (could not mkdir)");
            return Status::CANTCREAT;
        }

        $this->stdio->outln("Created: {$this->subdir}");
    }

    protected function getVars()
    {
        $name = strtolower($this->type);

        $primary = trim($this->getopt->get('--primary', "{$name}_id"));

        $default = "[" . PHP_EOL
                 . "            '$primary' => null," . PHP_EOL
                 . "        ]";

        $cols = $this->getopt->get('--cols', '*');
        if ($cols != '*') {

            $cols = explode(',', $cols);

            if (! in_array($primary, $cols)) {
                array_unshift($cols, $primary);
            }

            $default = "[" . PHP_EOL . "            '"
                  . implode("' => null," . PHP_EOL . "            '" , $cols)
                  . "' => null," . PHP_EOL . "        ]";
        }

        $cols = "[" . PHP_EOL . "            '"
              . implode("'," . PHP_EOL . "            '" , (array) $cols)
              . "'," . PHP_EOL . "        ]";

        return [
            '{NAMESPACE}' => $this->namespace,
            '{TYPE}' => $this->type,
            '{TABLE}' => trim($this->getopt->get(
                '--table',
                $name
            )),
            '{COLS}' => $cols,
            '{DEFAULT}' => $default,
            '{AUTOINC}' => $this->getopt->get('--noautoinc')
                ? 'false'
                : 'true',
            '{PRIMARY}' => trim($this->getopt->get(
                '--primary',
                "{$name}_id"
            )),
        ];

    }

    public function __invoke()
    {
        $exit = $this->setGetopt();
        if ($exit) {
            return $exit;
        }

        $exit = $this->setNamespace();
        if ($exit) {
            return $exit;
        }

        $exit = $this->setDir();
        if ($exit) {
            return $exit;
        }


        // create the classes
        $vars = $this->getVars();
        foreach ($this->classes as $class) {
            $file = $this->subdir . $this->type . $class . '.php';
            if (file_exists($file)) {
                $this->stdio->outln("SKIPPED: $file (already exists)");
                continue;
            }

            $prop = lcfirst($class); // RowFilter => rowFilter
            $code = strtr($this->$prop, $vars);
            file_put_contents($file, $code);
            $this->stdio->outln("Created: $file");
        }

        $this->stdio->outln('Done!');
        return Status::SUCCESS;
    }

    protected $table = <<<TABLE
<?php
namespace {NAMESPACE};

use Atlas\Table\AbstractTable;

class {TYPE}Table extends AbstractTable
{
    public function getTable()
    {
        return '{TABLE}';
    }

    public function getPrimary()
    {
        return '{PRIMARY}';
    }

    public function getAutoinc()
    {
        return {AUTOINC};
    }

    public function getCols()
    {
        return {COLS};
    }

    public function getDefault()
    {
        return {DEFAULT};
    }

    public function getRowClass()
    {
        return '{NAMESPACE}\\{TYPE}Row';
    }

    public function getRowSetClass()
    {
        return '{NAMESPACE}\\{TYPE}RowSet';
    }

    public function getRowIdentityClass()
    {
        return '{NAMESPACE}\\{TYPE}RowIdentity';
    }
}

TABLE;

    protected $row = <<<ROW
<?php
namespace {NAMESPACE};

use Atlas\Table\AbstractRow;

class {TYPE}Row extends AbstractRow
{
}

ROW;

    protected $rowIdentity = <<<ROW_IDENTITY
<?php
namespace {NAMESPACE};

use Atlas\Table\RowIdentity;

class {TYPE}RowIdentity extends RowIdentity
{
}

ROW_IDENTITY;

    protected $rowSet = <<<ROW_SET
<?php
namespace {NAMESPACE};

use Atlas\Table\AbstractRowSet;

class {TYPE}RowSet extends AbstractRowSet
{
}

ROW_SET;

    protected $rowFilter = <<<ROW_FILTER
<?php
namespace {NAMESPACE};

use Atlas\Table\RowFilter;

class {TYPE}RowFilter extends RowFilter
{
}

ROW_FILTER;

    protected $mapper = <<<MAPPER
<?php
namespace {NAMESPACE};

use Atlas\Mapper\Mapper;

class {TYPE}Mapper extends Mapper
{
}

MAPPER;

    protected $record = <<<RECORD
<?php
namespace {NAMESPACE};

use Atlas\Mapper\Record;

class {TYPE}Record extends Record
{
}

RECORD;

    protected $recordSet = <<<RECORD_SET
<?php
namespace {NAMESPACE};

use Atlas\Mapper\RecordSet;

class {TYPE}RecordSet extends RecordSet
{
}

RECORD_SET;

}
