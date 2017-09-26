<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdoInterface;

/**
 * DO NOT retain or memoize connections retrieved from the Manager. If you do,
 * automatic setting and tracking of transactions WILL NOT WORK. Instead, call
 * getRead() and getWrite() EACH TIME YOU NEED A CONNECTION.
 *
 * Note that this is a TABLE-ORIENTED connection manager. It is intended for use
 * by the Table objects, not general-purpose database interactions.
 */
class ConnectionManager
{
    const ALWAYS = 'ALWAYS';
    const WHILE_WRITING = 'WHILE_WRITING';
    const NEVER = 'NEVER';

    protected $spec = [
        'read' => [],
        'write' => [],
    ];

    protected $conn = [
        'read' => [],
        'write' => [],
    ];

    protected $readTransactions = false;

    protected $writeTransactions = true;

    protected $readFromWrite = 'NEVER';

    protected $writing = [];

    public function __construct(ConnectionLocator $connectionLocator)
    {
        $this->connectionLocator = $connectionLocator;
    }

    public function getConnectionLocator() : ConnectionLocator
    {
        return $this->connectionLocator;
    }

    public function setReadTransactions(bool $readTransactions = true) : void
    {
        $this->readTransactions = $readTransactions;
        // should this blow up when TURNING OFF transactions, and transactions already exist?
    }

    public function hasReadTransactions() : bool
    {
        return $this->readTransactions;
    }

    // should this even be available? writes should *always* be transacted?
    public function setWriteTransactions(bool $writeTransactions = true) : void
    {
        $this->writeTransactions = $writeTransactions;
        // should this blow up when TURNING OFF transactions, and transactions already exist?
    }

    public function hasWriteTransactions() : bool
    {
        return $this->writeTransactions;
    }

    public function setReadFromWrite(string $readFromWrite) : void
    {
        $guard = [static::ALWAYS, static::WHILE_WRITING, static::NEVER];
        if (! in_array($readFromWrite, $guard)) {
            throw Exception::unexpectedOption($readFromWrite, $guard);
        }

        $this->readFromWrite = $readFromWrite;
    }

    public function getReadFromWrite() : string
    {
        return $this->readFromWrite;
    }

    public function setRead(string $tableClass, ...$names) : void
    {
        $this->spec['read'][$tableClass] = $names;
    }

    public function setWrite(string $tableClass, ...$names) : void
    {
        $this->spec['write'][$tableClass] = $names;
    }

    public function getRead(string $tableClass) : ExtendedPdoInterface
    {
        if ($this->readFromWrite($tableClass)) {
            return $this->getWrite($tableClass);
        }

        $conn = $this->getConnection('read', $tableClass);

        if ($this->hasReadTransactions() && ! $conn->inTransaction()) {
            $conn->beginTransaction();
        }

        return $conn;
    }

    protected function readFromWrite($tableClass) : bool
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

    public function getWrite(string $tableClass) : ExtendedPdoInterface
    {
        $conn = $this->getConnection('write', $tableClass);

        if ($this->hasWriteTransactions() && ! $conn->inTransaction()) {
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
        foreach ($this->conn as $type => $table_conn) {
            foreach ($table_conn as $table => $conn) {
                if ($conn->inTransaction()) {
                    $conn->$method();
                }
            }
        }
    }

    protected function getConnection(string $type, string $tableClass) : ExtendedPdoInterface
    {
        if (isset($this->conn[$type][$tableClass])) {
            return $this->conn[$type][$tableClass];
        }

        $name = null;
        if (isset($this->spec[$type][$tableClass])) {
            $key = array_rand($this->spec[$type][$tableClass]);
            $name = $this->spec[$type][$tableClass][$key];
        }

        $func = 'get' . ucfirst($type);
        $conn = $this->connectionLocator->$func($name);
        $this->conn[$type][$tableClass] = $conn;
        return $conn;
    }
}
