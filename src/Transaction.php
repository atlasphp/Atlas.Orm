<?php
namespace Atlas\Orm;

use Atlas\Orm\Exception;
use Atlas\Orm\Mapper\RecordInterface;
use Atlas\Orm\Mapper\MapperLocator;
use SplObjectStorage;

class Transaction
{
    /**
     *
     * A MapperLocator to insert, update, and delete records.
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
     * Adds a callable as part of the transaction plan.
     *
     * @param string $label A label for the planned work.
     *
     * @param callable $callable The callable to invoke.
     *
     * @param mixed ...$args Arguments to pass to the callable.
     *
     */
    public function plan($label, callable $callable, ...$args)
    {
        $this->plan[] = $this->newWork($label, $callable, $args);
        return $this;
    }

    /**
     *
     * Specifies a record to insert as part of the transaction.
     *
     * @param RecordInterface $record The record to insert.
     *
     * @return null
     *
     */
    public function insert(RecordInterface $record)
    {
        $this->planRecordWork('insert', $record);
        return $this;
    }

    /**
     *
     * Specifies a record to update as part of the transaction.
     *
     * @param RecordInterface $record The record to update.
     *
     * @return null
     *
     */
    public function update(RecordInterface $record)
    {
        $this->planRecordWork('update', $record);
        return $this;
    }

    /**
     *
     * Specifies a record to delete as part of the transaction.
     *
     * @param RecordInterface $record The record to delete.
     *
     * @return null
     *
     */
    public function delete(RecordInterface $record)
    {
        $this->planRecordWork('delete', $record);
        return $this;
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
     * @return null
     *
     */
    protected function planRecordWork($method, RecordInterface $record)
    {
        $mapper = $this->mapperLocator->get($record->getMapperClass());
        $this->connections->attach($mapper->getWriteConnection());

        $label = "$method " . get_class($record) . " via " . get_class($mapper);
        $callable = [$mapper, $method];
        $this->plan($label, $callable, $record);
    }

    /**
     *
     * Returns a new Work instance.
     *
     * @param string $label A label for the planned work.
     *
     * @param callable $callable The callable to invoke for the work.
     *
     * @param array $args Arguments to pass to the callable.
     *
     * @return Work
     *
     */
    protected function newWork($label, callable $callable, array $args)
    {
        return new Work($label, $callable, $args);
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
            $this->execPlan();
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->exception = $e;
            $this->rollBack();
            return false;
        }
    }

    /**
     *
     * Executes all planned work.
     *
     * @return mixed
     *
     */
    protected function execPlan()
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
