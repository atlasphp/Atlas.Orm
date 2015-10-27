<?php
namespace Atlas\Command;

use Aura\Cli\Context;
use Aura\Cli\Status;
use Aura\Cli\Stdio;
use Aura\SqlSchema\ColumnFactory;
use Exception;
use PDO;

class Skeleton
{
    protected $context;
    protected $stdio;
    protected $getopt;
    protected $namespace;
    protected $dir;
    protected $subdir;
    protected $type;
    protected $conn;
    protected $pdo;
    protected $vars;
    protected $templates;

    public function __construct(Context $context, Stdio $stdio, $dir)
    {
        $this->context= $context;
        $this->stdio = $stdio;
        $this->dir = $dir;
    }

    public function __invoke()
    {
        $methods = [
            'setGetopt',
            'setConn',
            'setNamespace',
            'setDir',
            'setTemplates',
            'setVars',
            'createClasses',
        ];

        foreach ($methods as $method) {
            $exit = $this->$method();
            if ($exit) {
                return $exit;
            }
        }

        $this->stdio->outln('Done!');
        return Status::SUCCESS;
    }

    protected function setGetopt()
    {
        $options = [
            'conn:',
            'cols:',
            'dir:',
            'noautoinc',
            'primary:',
            'table:',
        ];
        $this->getopt = $this->context->getopt($options);

        $conn = $this->getopt->get('--conn', false);
        $others = $this->getopt->get('--cols', false)
               || $this->getopt->get('--noautoinc', false)
               || $this->getopt->get('--primary', false);
        if ($conn && $others) {
            $this->stdio->errln('Cannot specify --conn at the same time as --cols, --noautoinc, or --primary.');
            return Status::USAGE;
        }

        if (! $this->getopt->hasErrors()) {
            return;
        }

        $errors = $this->getopt->getErrors();
        foreach ($errors as $error) {
            $this->stdio->errln($error->getMessage());
        }
        return STATUS::USAGE;
    }

    protected function setConn()
    {
        $file = $this->getopt->get('--conn', false);
        if (! $file) {
            return;
        }

        if (! file_exists($file) || ! is_readable($file)) {
            $this->stdio->errln("Connection config file '$file' does not exist or is not readable.");
            return Status::NOINPUT;
        }

        $this->conn = $this->requireFile($file);
        if (! $this->conn || ! is_array($this->conn)) {
            $this->stdio->errln("Connection config file '$file' did not return an array of PDO parameters.");
            return Status::CONFIG;
        }

        try {
            $this->pdo = $this->newPdo();
        } catch (Exception $e) {
            $this->stdio->errln($e->getMessage());
            return Status::UNAVAILABLE;
        }
    }

    // require the file in an isolated scope
    protected function requireFile($file)
    {
        $require = function () use ($file) { return require $file; };
        return $require();
    }

    protected function newPdo()
    {
        return new PDO(...$this->conn);
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

    protected function setVars()
    {
        if ($this->pdo) {
            return $this->setVarsFromConn();
        }

        return $this->setVarsFromGetopt();
    }

    protected function setVarsFromConn()
    {
        $dsn = $this->conn[0];
        $pos = strpos($dsn, ':');
        $type = ucfirst(strtolower(substr($dsn, 0, $pos)));
        $class = "Aura\\SqlSchema\\{$type}Schema";
        $schema = new $class($this->pdo, new ColumnFactory());

        $table = trim($this->getopt->get('--table', strtolower($this->type)));
        $tables = $schema->fetchTableList();
        if (! in_array($table, $tables)) {
            $this->stdio->errln("Table '{$table}' not found.");
            return Status::FAILURE;
        }

        $primary = null;
        $autoinc = 'false';
        $list = [];
        foreach ($schema->fetchTableCols($table) as $col) {
            $list[$col->name] = $col->default;
            if ($col->primary) {
                $primary = $col->name;
            }
            if ($col->autoinc) {
                $autoinc = 'true';
            }
        }

        $cols = "[" . PHP_EOL;
        $default = "[" . PHP_EOL;
        foreach ($list as $col => $val) {
            $val = ($val === null) ? 'null' : var_export($val, true);
            $cols .= "            '$col'," . PHP_EOL;
            $default .= "            '$col' => $val," . PHP_EOL;
        }
        $cols .= "        ]";
        $default .= "        ]";

        $this->vars = [
            '{NAMESPACE}' => $this->namespace,
            '{TYPE}' => $this->type,
            '{TABLE}' => $table,
            '{COLS}' => $cols,
            '{DEFAULT}' => $default,
            '{AUTOINC}' => $autoinc,
            '{PRIMARY}' => $primary,
        ];
    }

    protected function setVarsFromGetopt()
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

        $this->vars = [
            '{NAMESPACE}' => $this->namespace,
            '{TYPE}' => $this->type,
            '{TABLE}' => trim($this->getopt->get('--table', $name)),
            '{COLS}' => $cols,
            '{DEFAULT}' => $default,
            '{AUTOINC}' => $this->getopt->get('--noautoinc') ? 'false' : 'true',
            '{PRIMARY}' => trim($this->getopt->get('--primary', "{$name}_id")),
        ];

    }

    protected function createClasses()
    {
        foreach ($this->templates as $class => $template) {
            $this->createClass($class, $template);
        }
    }

    protected function createClass($class, $template)
    {
        $file = $this->subdir . $this->type . $class . '.php';
        if (file_exists($file)) {
            $this->stdio->outln("SKIPPED: $file (already exists)");
            return;
        }

        $code = strtr($template, $this->vars);
        file_put_contents($file, $code);
        $this->stdio->outln("Created: $file");
    }

    protected function setTemplates()
    {
        $this->templates['Table'] = <<<TPL
<?php
namespace {NAMESPACE};

use Atlas\Table\AbstractTable;
use Atlas\Table\IdentityMap;
use Aura\Sql\ConnectionLocator;
use Aura\SqlQuery\QueryFactory;

class {TYPE}Table extends AbstractTable
{
    public function __construct(
        ConnectionLocator \$connectionLocator,
        QueryFactory \$queryFactory,
        IdentityMap \$identityMap,
        {TYPE}RowFactory \$rowFactory,
        {TYPE}RowFilter \$rowFilter
    ) {
        parent::__construct(
            \$connectionLocator,
            \$queryFactory,
            \$identityMap,
            \$rowFactory,
            \$rowFilter
        );
    }

    public function getTable()
    {
        return '{TABLE}';
    }

    public function getAutoinc()
    {
        return {AUTOINC};
    }

    public function getCols()
    {
        return {COLS};
    }
}

TPL;

        $this->templates['Row'] = <<<TPL
<?php
namespace {NAMESPACE};

use Atlas\Table\AbstractRow;

class {TYPE}Row extends AbstractRow
{
}

TPL;

        $this->templates['RowIdentity'] = <<<TPL
<?php
namespace {NAMESPACE};

use Atlas\Table\AbstractRowIdentity;

class {TYPE}RowIdentity extends AbstractRowIdentity
{
}

TPL;

        $this->templates['RowSet'] = <<<TPL
<?php
namespace {NAMESPACE};

use Atlas\Table\AbstractRowSet;

class {TYPE}RowSet extends AbstractRowSet
{
}

TPL;

        $this->templates['RowFactory'] = <<<TPL
<?php
namespace {NAMESPACE};

use Atlas\Table\AbstractRowFactory;

class {TYPE}RowFactory extends AbstractRowFactory
{
    public function getPrimary()
    {
        return '{PRIMARY}';
    }

    public function getDefault()
    {
        return {DEFAULT};
    }
}

TPL;

        $this->templates['RowFilter'] = <<<TPL
<?php
namespace {NAMESPACE};

use Atlas\Table\AbstractRow;
use Atlas\Table\AbstractRowFilter;

class {TYPE}RowFilter extends AbstractRowFilter
{
    public function forInsert(AbstractRow \$row)
    {
        // do nothing
    }

    public function forUpdate(AbstractRow \$row)
    {
        // do nothing
    }

    public function forDelete(AbstractRow \$row)
    {
        // do nothing
    }
}

TPL;

        $this->templates['Mapper'] = <<<TPL
<?php
namespace {NAMESPACE};

use Atlas\Mapper\AbstractMapper;

class {TYPE}Mapper extends AbstractMapper
{
    public function __construct(
        {TYPE}Table \$table,
        {TYPE}RecordFactory \$recordFactory,
        {TYPE}RecordFilter \$recordFilter,
        {TYPE}Relations \$relations
    ) {
        parent::__construct(
            \$table,
            \$recordFactory,
            \$recordFilter,
            \$relations
        );
    }
}

TPL;

        $this->templates['Record'] = <<<TPL
<?php
namespace {NAMESPACE};

use Atlas\Mapper\AbstractRecord;

class {TYPE}Record extends AbstractRecord
{
}

TPL;

        $this->templates['RecordSet'] = <<<TPL
<?php
namespace {NAMESPACE};

use Atlas\Mapper\AbstractRecordSet;

class {TYPE}RecordSet extends AbstractRecordSet
{
}

TPL;

        $this->templates['RecordFactory'] = <<<TPL
<?php
namespace {NAMESPACE};

use Atlas\Mapper\AbstractRecordFactory;

class {TYPE}RecordFactory extends AbstractRecordFactory
{
}

TPL;

        $this->templates['RecordFilter'] = <<<TPL
<?php
namespace {NAMESPACE};

use Atlas\Mapper\AbstractRecord;
use Atlas\Mapper\AbstractRecordFilter;

class {TYPE}RecordFilter extends AbstractRecordFilter
{
    public function forInsert(AbstractRecord \$record)
    {
        // do nothing
    }

    public function forUpdate(AbstractRecord \$record)
    {
        // do nothing
    }

    public function forDelete(AbstractRecord \$record)
    {
        // do nothing
    }
}

TPL;
        $this->templates['Relations'] = <<<TPL
<?php
namespace {NAMESPACE};

use Atlas\Mapper\AbstractRelations;

class {TYPE}Relations extends AbstractRelations
{
    protected function setRelations()
    {
        // no relations
    }
}

TPL;

    }
}
