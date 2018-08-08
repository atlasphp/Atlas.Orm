<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
declare(strict_types=1);

namespace Atlas\Orm;

use Atlas\Mapper\Mapper;
use Atlas\Mapper\MapperLocator;
use Atlas\Mapper\MapperQueryFactory;
use Atlas\Mapper\MapperSelect;
use Atlas\Mapper\Record;
use Atlas\Mapper\RecordSet;
use Atlas\Pdo\ConnectionLocator;
use Atlas\Table\TableLocator;
use Atlas\Orm\Transaction\AutoCommit;
use Atlas\Orm\Transaction\Transaction;

class Atlas
{
    protected $mapperLocator;

    protected $transaction;

    public static function new(...$args) : Atlas
    {
        $transactionClass = AutoCommit::CLASS;

        $end = end($args);
        if (is_string($end) && is_subclass_of($end, Transaction::CLASS)) {
            $transactionClass = array_pop($args);
        }

        $connectionLocator = ConnectionLocator::new(...$args);

        $tableLocator = new TableLocator(
            $connectionLocator,
            new MapperQueryFactory()
        );

        return new Atlas(
            new MapperLocator($tableLocator),
            new $transactionClass($connectionLocator)
        );
    }

    public function __construct(
        MapperLocator $mapperLocator,
        Transaction $transaction
    ) {
        $this->mapperLocator = $mapperLocator;
        $this->transaction = $transaction;
    }

    public function mapper(string $mapperClass) : Mapper
    {
        return $this->mapperLocator->get($mapperClass);
    }

    public function newRecord(string $mapperClass, array $fields = []) : Record
    {
        return $this->mapper($mapperClass)->newRecord($fields);
    }

    public function newRecords(string $mapperClass, array $fieldSets) : array
    {
        return $this->mapper($mapperClass)->newRecords($fieldSets);
    }

    public function newRecordSet(string $mapperClass) : RecordSet
    {
        return $this->mapper($mapperClass)->newRecordSet();
    }

    public function fetchRecord(string $mapperClass, $primaryVal, array $with = []) : ?Record
    {
        return $this->read($mapperClass, __FUNCTION__, $primaryVal, $with);
    }

    public function fetchRecordBy(string $mapperClass, array $whereEquals, array $with = []) : ?Record
    {
        return $this->read($mapperClass, __FUNCTION__, $whereEquals, $with);
    }

    public function fetchRecords(string $mapperClass, array $primaryVals, array $with = []) : array
    {
        return $this->read($mapperClass, __FUNCTION__, $primaryVals, $with);
    }

    public function fetchRecordsBy(string $mapperClass, array $whereEquals, array $with = []) : array
    {
        return $this->read($mapperClass, __FUNCTION__, $whereEquals, $with);
    }

    public function fetchRecordSet(string $mapperClass, array $primaryVals, array $with = []) : ?RecordSet
    {
        return $this->read($mapperClass, __FUNCTION__, $primaryVals, $with);
    }

    public function fetchRecordSetBy(string $mapperClass, array $whereEquals, array $with = []) : ?RecordSet
    {
        return $this->read($mapperClass, __FUNCTION__, $whereEquals, $with);
    }

    public function select(string $mapperClass, array $whereEquals = []) : MapperSelect
    {
        return $this->read($mapperClass, __FUNCTION__, $whereEquals);
    }

    public function insert(Record $record) : void
    {
        $this->write(__FUNCTION__, $record);
    }

    public function update(Record $record) : void
    {
        $this->write(__FUNCTION__, $record);
    }

    public function delete(Record $record) : void
    {
        $this->write(__FUNCTION__, $record);
    }

    public function persist(Record $record) : void
    {
        $this->write(__FUNCTION__, $record);
    }

    public function persistRecords(array $records) : void
    {
        foreach ($records as $record) {
            $this->persist($record);
        }
    }

    public function persistRecordSet(RecordSet $recordSet) : void
    {
        foreach ($recordSet as $record) {
            $this->persist($record);
        }
    }

    public function beginTransaction() : void
    {
        $this->transaction->beginTransaction();
    }

    public function commit() : void
    {
        $this->transaction->commit();
    }

    public function rollBack() : void
    {
        $this->transaction->rollBack();
    }

    public function logQueries($logQueries = true) : void
    {
        $this->mapperLocator
            ->getTableLocator()
            ->getConnectionLocator()
            ->logQueries($logQueries);
    }

    public function getQueries() : array
    {
        return $this->mapperLocator
            ->getTableLocator()
            ->getConnectionLocator()
            ->getQueries();
    }

    public function setQueryLogger(callable $queryLogger) : void
    {
        $this->mapperLocator
            ->getTableLocator()
            ->getConnectionLocator()
            ->setQueryLogger($queryLogger);
    }

    protected function read(string $mapperClass, string $method, ...$params)
    {
        $mapper = $this->mapper($mapperClass);
        return $this->transaction->read($mapper, $method, $params);
    }

    protected function write(string $method, Record $record) : void
    {
        $mapper = $this->mapper($record->getMapperClass());
        $this->transaction->write($mapper, $method, $record);
    }
}
