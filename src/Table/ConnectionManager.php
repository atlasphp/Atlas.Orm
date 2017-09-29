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

    /**
     *
     * Do reads occur over write connections?
     *
     * @return string 'ALWAYS', 'WHILE_WRITING', or 'NEVER'.
     *
     */
    public function getReadFromWrite() : string
    {
        return $this->readFromWrite;
    }

    /**
     *
     * Sets one or more specific read connections for a table class.
     *
     * @param string $tableClass The table class to set the connection for.
     *
     * @param string[] ...$names One or more named read connections in the
     * ConnectionLocator.
     *
     */
    public function setRead(string $tableClass, ...$names) : void
    {
        $this->spec['read'][$tableClass] = $names;
    }

    /**
     *
     * Sets one or more specific write connections for a table class.
     *
     * @param string $tableClass The table class to set the connection for.
     *
     * @param string[] ...$names One or more named write connections in the
     * ConnectionLocator.
     *
     */
    public function setWrite(string $tableClass, ...$names) : void
    {
        $this->spec['write'][$tableClass] = $names;
    }

    /**
     *
     * Gets a read connection for a table class.
     *
     * If read-from-write is active, this returns a write connection for the
     * table class instead of a read connection.
     *
     * If multiple connections are specified, this will pick one of them at
     * random.
     *
     * If no connection for the table is specified, this will let the
     * ConnectionLocator choose.
     *
     * If transactions are active and the connection is not in a transaction,
     * this will begin a transaction on that connection.
     *
     * @param string $tableClass The table class to get the connection for.
     *
     * @return ExtendedPdoInterface
     *
     */
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

    /**
     *
     * Is read-from-write active for a particular table?
     *
     * @param string $tableClass The table class to check on.
     *
     * @return bool
     *
     */
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

    /**
     *
     * Gets a write connection for a table class.
     *
     * If multiple connections are specified, this will pick one of them at
     * random.
     *
     * If no connection for the table is specified, this will let the
     * ConnectionLocator choose.
     *
     * If transactions are active and the connection is not in a transaction,
     * this will begin a transaction on that connection.
     *
     * Finally, this tracks which table classes have asked for a write
     * connection since the end of the last transaction.
     *
     * @param string $tableClass The table class to get the connection for.
     *
     * @return ExtendedPdoInterface
     *
     */
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

    /**
     *
     * Commits all transactions on all connections, and clears all tracking of
     * which table classes are using write connections.
     *
     * @return void
     *
     */
    public function commit() : void
    {
        $this->endTransactions('commit');
    }

    /**
     *
     * Rolls back all transactions on all connections, and clears all tracking
     * of which table classes are using write connections.
     *
     * @return void
     *
     */
    public function rollBack() : void
    {
        $this->endTransactions('rollBack');
    }

    /**
     *
     * Ends all transactions on all connections, and clears all tracking
     * of which table classes are using write connections.
     *
     * @param string $method The method to call on the connection.
     *
     * @return void
     *
     */
    protected function endTransactions($method)
    {
        foreach ($this->conn as $type => $table_conn) {
            foreach ($table_conn as $table => $conn) {
                if ($conn->inTransaction()) {
                    $conn->$method();
                }
            }
        }
        $this->writing = [];
    }

    /**
     *
     * Get a read or write connection for a table class.
     *
     * @param string $type The connection type (read or write).
     *
     * @param string $tableClass The table class to get the connection for.
     *
     * @return ExtendedPdoInterface
     *
     */
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
