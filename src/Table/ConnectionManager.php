<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;
use Aura\Sql\ConnectionLocator;
use Aura\Sql\ExtendedPdoInterface;

/**
 *
 * A connection manager intended for use by Table objects; it is not for
 * general-purpose database interactions.
 *
 * Warning: DO NOT retain or memoize connections retrieved from this Manager.
 * If you do, automatic setting and tracking of transactions WILL NOT WORK.
 * Instead, call getRead() and getWrite() EACH TIME YOU NEED A CONNECTION.
 *
 * @package atlas/orm
 *
 */
class ConnectionManager
{
    /**
     * Always read from a write connection, not a read connection.
     */
    const ALWAYS = 'ALWAYS';

    /**
     * Read from a write connections *only while writing*.
     */
    const WHILE_WRITING = 'WHILE_WRITING';

    /**
     * Never read from a write connection.
     */
    const NEVER = 'NEVER';

    /**
     *
     * Specifications for read and write connections; which table classes should
     * use which named ConnectionLocator connections.
     *
     * @var array
     *
     */
    protected $spec = [
        'read' => [],
        'write' => [],
    ];

    /**
     *
     * Actual read and write connection instances, per table class.
     *
     * @var array
     *
     */
    protected $conn = [
        'read' => [],
        'write' => [],
    ];

    /**
     *
     * Use transactions on all read connections?
     *
     * @var bool
     *
     */
    protected $readTransactions = false;

    /**
     *
     * Use transactions on all write connections?
     *
     * @var bool
     *
     */
    protected $writeTransactions = true;

    /**
     *
     * When, if ever, should a "read" connection be replaced with a "write"
     * connection?
     *
     * @var string
     *
     */
    protected $readFromWrite = 'NEVER';

    /**
     *
     * What tables are usign write connections?
     *
     * @var array
     *
     */
    protected $writing = [];

    /**
     *
     * Constructor.
     *
     * @param ConnectionLocator $connectionLocator A locator for the underlying
     * ExtendedPdo connections.
     *
     */
    public function __construct(ConnectionLocator $connectionLocator)
    {
        $this->connectionLocator = $connectionLocator;
    }

    /**
     *
     * Returns the ConnectionLocator.
     *
     * @return ConnectionLocator
     *
     */
    public function getConnectionLocator() : ConnectionLocator
    {
        return $this->connectionLocator;
    }

    /**
     *
     * Should all read connections use transactions?
     *
     * @param bool $readTransactions True to enable; false to disable.
     *
     */
    public function setReadTransactions(bool $readTransactions = true) : void
    {
        $this->readTransactions = $readTransactions;
        // should this blow up when TURNING OFF transactions, and transactions already exist?
    }

    /**
     *
     * Are transactions on read connections enabled?
     *
     * @return bool
     *
     */
    public function hasReadTransactions() : bool
    {
        return $this->readTransactions;
    }

    /**
     *
     * Should all write connections use transactions?
     *
     * @param bool $writeTransactions True to enable; false to disable.
     *
     */
    public function setWriteTransactions(bool $writeTransactions = true) : void
    {
        // should this even be available? writes should *always* be transacted?
        $this->writeTransactions = $writeTransactions;
        // should this blow up when TURNING OFF transactions, and transactions already exist?
    }

    /**
     *
     * Are transactions on write connections enabled?
     *
     * @return bool
     *
     */
    public function hasWriteTransactions() : bool
    {
        return $this->writeTransactions;
    }

    /**
     *
     * When, if ever, should reads occur over write connections?
     *
     * @param string $readFromWrite 'ALWAYS', 'WHILE_WRITING', or 'NEVER'.
     *
     */
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
