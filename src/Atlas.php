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

class Atlas
{
    protected $mapperLocator;

    protected $transactionStrategy;

    public static function new(...$args) : Atlas
    {
        $end = end($args);
        if (is_string($end) && is_subclass_of($end, TransactionStrategy::CLASS)) {
            $transactionClass = array_pop($args);
        } else {
            $transactionClass = MiniTransaction::CLASS;
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
        TransactionStrategy $transactionStrategy
    ) {
        $this->mapperLocator = $mapperLocator;
        $this->transactionStrategy = $transactionStrategy;
    }

    public function mapper(string $mapperClass) : Mapper
    {
        return $this->mapperLocator->get($mapperClass);
    }

    public function newRecord(string $mapperClass, array $cols = []) : Record
    {
        return $this->mapper($mapperClass)->newRecord($cols);
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

    public function insert(Record $record) : bool
    {
        return $this->write(__FUNCTION__, $record);
    }

    public function update(Record $record) : bool
    {
        return $this->write(__FUNCTION__, $record);
    }

    public function delete(Record $record) : bool
    {
        return $this->write(__FUNCTION__, $record);
    }

    public function persist(Record $record) : bool
    {
        return $this->write(__FUNCTION__, $record);
    }

    public function commit() : void
    {
        $this->transactionStrategy->commit();
    }

    public function rollBack() : void
    {
        $this->transactionStrategy->rollBack();
    }

    protected function read(string $mapperClass, string $method, ...$params)
    {
        $mapper = $this->mapper($mapperClass);
        return $this->transactionStrategy->read($mapper, $method, $params);
    }

    protected function write(string $method, Record $record)
    {
        $mapper = $this->mapper($record->getMapperClass());
        return $this->transactionStrategy->write($mapper, $method, $record);
    }
}
