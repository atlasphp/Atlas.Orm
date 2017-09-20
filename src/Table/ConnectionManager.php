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

    protected $inTransaction = false;

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
        return $this->getTableConnection('read', $tableClass);
    }

    public function getWriteForTable(string $tableClass) : ExtendedPdoInterface
    {
        $conn = $this->getTableConnection('write', $tableClass);
        if ($this->inTransaction && ! $conn->inTransaction()) {
            $conn->beginTransaction();
        }
        return $conn;
    }

    public function beginTransaction() : void
    {
        $this->inTransaction = true;
    }

    public function commit() : void
    {
        foreach ($this->tableConn['write'] as $conn) {
            if ($conn->inTransaction()) {
                $conn->commit();
            }
        }
        $this->inTransaction = false;
    }

    public function rollBack() : void
    {
        foreach ($this->tableConn['write'] as $conn) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
        }
        $this->inTransaction = false;
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
