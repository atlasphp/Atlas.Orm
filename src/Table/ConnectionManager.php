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

    protected $transactionsOnReads = false;

    protected $transactionsOnWrites = true;

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

    public function setTransactionsOnReads(bool $transactionsOnReads) : void
    {
        $this->transactionsOnReads = $transactionsOnReads;
    }

    public function hasTransactionsOnReads() : bool
    {
        return $this->transactionsOnReads;
    }

    public function setTransactionsOnWrites(bool $transactionsOnWrites) : void
    {
        $this->transactionsOnWrites = $transactionsOnWrites;
    }

    public function hasTransactionsOnWrites() : bool
    {
        return $this->transactionsOnWrites;
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
        if ($this->readFromWriteForTable($tableClass)) {
            return $this->getWriteForTable($tableClass);
        }

        $conn = $this->getTableConnection('read', $tableClass);

        if ($this->hasTransactionsOnReads() && ! $conn->inTransaction()) {
            $conn->beginTransaction();
        }

        return $conn;
    }

    public function readFromWriteForTable($tableClass) : bool
    {
        if ($this->readFromWrite == static::NEVER) {
            return false;
        }

        if ($this->readFromWrite == static::ALWAYS) {
            return true;
        }

        return $this->readFromWrite == static::WHILE_WRITING
            && isset($this->writing[$tableClass]);
    }

    public function getWriteForTable(string $tableClass) : ExtendedPdoInterface
    {
        $conn = $this->getTableConnection('write', $tableClass);

        if ($this->hasTransactionsOnWrites() && ! $conn->inTransaction()) {
            $conn->beginTransaction();
        }

        if (! isset($this->writing[$tableClass])) {
            $this->writing[$tableClass] = true;
        }

        return $conn;
    }

    public function commit() : void
    {
        $this->endTransactions('commit');
        $this->writing = [];
    }

    public function rollBack() : void
    {
        $this->endTransactions('rollBack');
        $this->writing = [];
    }

    protected function endTransactions($method)
    {
        foreach ($this->tableConn as $type => $conns) {
            foreach ($conns as $conn) {
                if ($conn->inTransaction()) {
                    $conn->$method();
                }
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
