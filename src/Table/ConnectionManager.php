<?php
namespace Atlas\Orm\Table;

use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdoInterface;

class ConnectionManager
{
    const ALWAYS = 'ALWAYS';
    const WHILE_WRITING = 'WHILE_WRITING';
    const NEVER = 'NEVER';

    protected $tableSpec = [
        'read' => [],
        'write' => [],
    ];

    protected $tableConn = [
        'read' => [],
        'write' => [],
    ];

    protected $readTransaction = false;

    protected $writeTransaction = true;

    protected $readFromWrite = 'NEVER';

    protected $writing = [];

    public function __construct(ConnectionLocator $connectionLocator)
    {
        $this->connectionLocator = $connectionLocator;
    }

    public function __call(string $func, array $args)
    {
        return $this->connectionLocator->$func(...$args);
    }

    public function getConnectionLocator() : ConnectionLocator
    {
        return $this->connectionLocator;
    }

    public function setReadTransaction(bool $readTransaction) : void
    {
        $this->readTransaction = $readTransaction;
    }

    public function getReadTransaction() : bool
    {
        return $this->readTransaction;
    }

    public function setWriteTransaction(bool $writeTransaction) : void
    {
        $this->writeTransaction = $writeTransaction;
    }

    public function getWriteTransaction() : bool
    {
        return $this->writeTransaction;
    }

    public function setReadFromWrite(string $readFromWrite) : void
    {
        $guard = [static::ALWAYS, static::WHILE_WRITING, static::NEVER];
        if (! in_array($readFromWrite, $guard)) {
            throw new \Exception("Unexpected value");
        }

        $this->readFromWrite = $readFromWrite;
    }

    public function getReadFromWrite() : string
    {
        return $this->readFromWrite;
    }

    public function willReadFromWrite($tableClass) : bool
    {
        if ($this->readFromWrite == static::NEVER) {
            return false;
        }

        if ($this->readFromWrite == static::ALWAYS) {
            return true;
        }

        return isset($this->writing[$tableClass]);
    }

    public function setReadForTable(string $tableClass, ...$names) : void
    {
        $this->tableSpec['read'][$tableClass] = $names;
    }

    public function setWriteForTable(string $tableClass, ...$names) : void
    {
        $this->tableSpec['write'][$tableClass] = $names;
    }

    public function getReadForTable(string $tableClass) : ExtendedPdoInterface
    {
        if ($this->willReadFromWrite($tableClass)) {
            return $this->getWriteForTable($tableClass);
        }

        $conn = $this->getTableConnection('read', $tableClass);
        if ($this->getReadTransaction() && ! $conn->inTransaction()) {
            $conn->beginTransaction();
        }
        return $conn;
    }

    public function getWriteForTable(string $tableClass) : ExtendedPdoInterface
    {
        $conn = $this->getTableConnection('write', $tableClass);
        if ($this->getWriteTransaction() && ! $conn->inTransaction()) {
            $conn->beginTransaction();
        }
        if (! isset($this->writing[$tableClass])) {
            $this->writing[$tableClass] = true;
        }
        return $conn;
    }

    protected function beginTransaction($conn)
    {

    }

    public function commit() : void
    {
        $this->endTransaction('write', 'commit');
        $this->endTransaction('read', 'commit');
        $this->writing = [];
    }

    public function rollBack() : void
    {
        $this->endTransaction('write', 'rollBack');
        $this->endTransaction('read', 'rollBack');
        $this->writing = [];
    }

    protected function endTransaction($type, $method)
    {
        foreach ($this->tableConn[$type] as $conn) {
            if ($conn->inTransaction()) {
                $conn->$method();
            }
        }
    }

    protected function getTableConnection(string $type, string $tableClass) : ExtendedPdoInterface
    {
        if (isset($this->tableConn[$type][$tableClass])) {
            return $this->tableConn[$type][$tableClass];
        }

        $name = null;
        if (isset($this->tableSpec[$type][$tableClass])) {
            $key = array_rand($this->tableSpec[$type][$tableClass]);
            $name = $this->tableSpec[$type][$tableClass][$key];
        }

        $func = 'get' . ucfirst($type);
        $conn = $this->$func($name);
        $this->tableConn[$type][$tableClass] = $conn;
        return $conn;
    }
}
