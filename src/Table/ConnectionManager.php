<?php
namespace Atlas\Orm\Table;

use Aura\Sql\ConnectionLocator;

class ConnectionManager
{
    protected $tableSpec = [];

    protected $tableConn = [];

    protected $inTransaction = false;

    public function __construct(ConnectionLocator $connectionLocator)
    {
        $this->connectionLocator = $connectionLocator;
    }

    public function __call($func, $args)
    {
        return $this->connectionLocator->$func(...$args);
    }

    public function getConnectionLocator()
    {
        return $this->connectionLocator;
    }

    public function setReadForTable($tableClass, ...$names)
    {
        $this->tableSpec[$tableClass]['read'] = $names;
    }

    public function setWriteForTable($tableClass, ...$names)
    {
        $this->tableSpec[$tableClass]['write'] = $names;
    }

    public function getReadForTable($tableClass)
    {
        return $this->getTableConnection($tableClass, 'read');
    }

    public function getWriteForTable($tableClass)
    {
        $conn = $this->getTableConnection($tableClass, 'write');
        if ($this->inTransaction && ! $conn->inTransaction()) {
            $conn->beginTransaction();
        }
        return $conn;
    }

    public function beginTransaction()
    {
        $this->inTransaction = true;
    }

    public function commit()
    {
        foreach ($this->tableConn['write'] as $conn) {
            if ($conn->inTransaction()) {
                $conn->commit();
            }
        }
        $this->inTransaction = false;
    }

    public function rollBack()
    {
        foreach ($this->tableConn['write'] as $conn) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
        }
        $this->inTransaction = false;
    }

    protected function getTableConnection($tableClass, $type)
    {
        if (isset($this->tableConn[$type][$tableClass])) {
            return $this->tableConn[$type][$tableClass];
        }

        $func = 'get' . ucfirst($type);
        $name = null;
        if (isset($this->tableSpec[$type][$tableClass])) {
            $name = array_rand($this->tableSpec[$type][$tableClass]);
        }

        $conn = $this->connectionLocator->$func($name);
        $this->tableConn[$type][$tableClass] = $conn;
        return $conn;
    }
}
