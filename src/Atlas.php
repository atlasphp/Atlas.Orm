<?php
namespace Atlas\Orm;

use Atlas\Orm\Mapper\MapperLocator;
use Atlas\Orm\Mapper\RecordInterface;
use Exception;

class Atlas
{
    protected $exception;
    protected $mapperLocator;
    protected $transaction;

    public function __construct(
        MapperLocator $mapperLocator,
        Transaction $prototypeTransaction
    ) {
        $this->mapperLocator = $mapperLocator;
        $this->prototypeTransaction = $prototypeTransaction;
    }

    public function mapper($mapperClass)
    {
        return $this->mapperLocator->get($mapperClass);
    }

    public function newRecord($mapperClass, array $cols = [])
    {
        return $this->mapper($mapperClass)->newRecord($cols);
    }

    public function newRecordSet($mapperClass)
    {
        return $this->mapper($mapperClass)->newRecordSet();
    }

    public function fetchRecord($mapperClass, $primaryVal, array $with = [])
    {
        return $this->mapper($mapperClass)->fetchRecord($primaryVal, $with);
    }

    public function fetchRecordBy($mapperClass, array $colsVals, array $with = [])
    {
        return $this->mapper($mapperClass)->fetchRecordBy($colsVals, $with);
    }

    public function fetchRecordSet($mapperClass, array $primaryVals, array $with = [])
    {
        return $this->mapper($mapperClass)->fetchRecordSet($primaryVals, $with);
    }

    public function fetchRecordSetBy($mapperClass, array $colsVals, array $with = [])
    {
        return $this->mapper($mapperClass)->fetchRecordSetBy($colsVals, $with);
    }

    public function select($mapperClass, array $colsVals = [])
    {
        return $this->mapper($mapperClass)->select($colsVals);
    }

    public function insert(RecordInterface $record)
    {
        return $this->transact('insert', $record);
    }

    public function update(RecordInterface $record)
    {
        return $this->transact('update', $record);
    }

    public function delete(RecordInterface $record)
    {
        return $this->transact('delete', $record);
    }

    public function newTransaction()
    {
        return clone $this->prototypeTransaction;
    }

    public function getException()
    {
        return $this->exception;
    }

    protected function transact($method, $record)
    {
        $this->exception = null;
        $transaction = $this->newTransaction();
        $transaction->$method($record);

        if (! $transaction->exec()) {
            $this->exception = $transaction->getException();
            return false;
        }

        $completed = $transaction->getCompleted();
        $work = $completed[0];
        return $work->getResult();
    }
}
