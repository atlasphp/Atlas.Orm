<?php
namespace Atlas\Orm\Table;

use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdoInterface;

class ConnectionManager
{
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

    public function setReadTransaction(bool $readTransaction)
    {
        $this->readTransaction = $readTransaction;
    }

    public function getReadTransaction() : bool
    {
        return $this->readTransaction;
    }

    public function setWriteTransaction(bool $writeTransaction)
    {
        $this->writeTransaction = $writeTransaction;
    }

    public function getWriteTransaction() : bool
    {
        return $this->writeTransaction;
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
        $this->writing = true;
        return $conn;
    }

    public function commit() : void
    {
        $this->endTransaction('write', 'commit');
        $this->endTransaction('read', 'commit');
        $this->writing = false;
    }

    public function rollBack() : void
    {
        $this->endTransaction('write', 'rollBack');
        $this->endTransaction('read', 'rollBack');
        $this->writing = false;
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
