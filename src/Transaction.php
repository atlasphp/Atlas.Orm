<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm;

use Atlas\Orm\Exception;
use Atlas\Orm\Mapper\RecordInterface;
use Atlas\Orm\Mapper\MapperLocator;
use SplObjectStorage;

/**
 *
 * A transaction to perform a unit-of-work.
 *
 * @package atlas/orm
 *
 */
class Transaction
{
    /**
     *
     * A MapperLocator to insert, update, and delete different records.
     *
     * @var MapperLocator
     *
     */
    protected $mapperLocator;

    /**
     *
     * Write connections extracted from the mappers.
     *
     * @var SplObjectStorage
     *
     */
    protected $connections;

    /**
     *
     * All planned work.
     *
     * @var array
     *
     */
    protected $plan = [];

    /**
     *
     * All completed work.
     *
     * @var array
     *
     */
    protected $completed = [];

    /**
     *
     * The exception that occurred during exec(), causing a rollback.
     *
     * @var Exception
     *
     */
    protected $exception;

    /**
     *
     * The work that caused the exception.
     *
     * @var Work
     *
     */
    protected $failure;

    /**
     *
     * Constructor.
     *
     * @param MapperLocator $mapperLocator The Mapper locator.
     *
     */
    public function __construct(MapperLocator $mapperLocator)
    {
        $this->mapperLocator = $mapperLocator;
        $this->connections = new SplObjectStorage();
    }

    /**
     *
     * Gets the planned work.
     *
     * @return array
     *
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     *
     * Gets the completed work.
     *
     * @return array
     *
     */
    public function getCompleted()
    {
        return $this->completed;
    }

    /**
     *
     * Gets the exception that caused a rollback in exec().
     *
     * @return Exception
     *
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     *
     * Gets the work that caused the exception in exec().
     *
     * @return Work
     *
     */
    public function getFailure()
    {
        return $this->failure;
    }

    /**
     *
     * Specifies a record to insert as part of the transaction.
     *
     * @param RecordInterface $record The record to insert.
     *
     * @return $this
     *
     */
    public function insert(RecordInterface $record)
    {
        return $this->plan('insert', $record);
    }

    /**
     *
     * Specifies a record to update as part of the transaction.
     *
     * @param RecordInterface $record The record to update.
     *
     * @return $this
     *
     */
    public function update(RecordInterface $record)
    {
        return $this->plan('update', $record);
    }

    /**
     *
     * Specifies a record to delete as part of the transaction.
     *
     * @param RecordInterface $record The record to delete.
     *
     * @return $this
     *
     */
    public function delete(RecordInterface $record)
    {
        return $this->plan('delete', $record);
    }

    /**
     *
     * Adds record-specific work to the transaction plan, and attaches the
     * relevant mapper connection.
     *
     * @param string $method The relevant mapper method to call.
     *
     * @param RecordInterface $record The record to work with.
     *
     * @return $this
     *
     */
    protected function plan($method, RecordInterface $record)
    {
        $mapper = $this->mapperLocator->get($record->getMapperClass());
        $this->connections->attach($mapper->getWriteConnection());

        $label = "$method " . get_class($record) . " via " . get_class($mapper);
        $callable = [$mapper, $method];
        $this->plan[] = $this->newWork($label, $callable, $record);
        return $this;
    }

    /**
     *
     * Returns a new Work instance.
     *
     * @param string $label A label for the planned work.
     *
     * @param callable $callable The callable to invoke for the work.
     *
     * @param RecordInterface $record The record to work with.
     *
     * @return Work
     *
     */
    protected function newWork($label, callable $callable, RecordInterface $record)
    {
        return new Work($label, $callable, $record);
    }

    /**
     *
     * Executes the transaction plan.
     *
     * @return bool True if the transaction succeeded, false if not.
     *
     * @throws Exception when attempting to re-execute a transaction.
     *
     * @todo Blow up if there is no plan.
     *
     * @todo Blow up if there are no write connections.
     *
     */
    public function exec()
    {
        $prior = $this->completed || $this->failure || $this->exception;
        if ($prior) {
            throw Exception::priorTransaction();
        }

        try {
            $this->begin();
            $this->work();
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->exception = $e;
            $this->rollBack();
            return false;
        }
    }

    /**
     *
     * Begins a transaction on all connections.
     *
     * @return null
     *
     */
    protected function begin()
    {
        foreach ($this->connections as $connection) {
            $connection->beginTransaction();
        }
    }

    /**
     *
     * Executes all planned work.
     *
     */
    protected function work()
    {
        foreach ($this->plan as $work) {
            $this->failure = $work;
            $work();
            $this->completed[] = $work;
            $this->failure = null;
        }
    }

    /**
     *
     * Commits the transaction on each connection.
     *
     * @return null
     *
     */
    protected function commit()
    {
        foreach ($this->connections as $connection) {
            $connection->commit();
        }
    }

    /**
     *
     * Rolls back the transaction on each connection.
     *
     * @return null
     *
     */
    protected function rollBack()
    {
        foreach ($this->connections as $connection) {
            $connection->rollBack();
        }
    }
}
