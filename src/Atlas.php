<?php
namespace Atlas;

use Atlas\Mapper\MapperLocator;
use Atlas\Mapper\AbstractRecord;
use Atlas\Table\TableSelect;
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

    public function mapper($class)
    {
        return $this->mapperLocator->get($class);
    }

    public function newRecord($class, array $data = [])
    {
        return $this->mapper($class)->newRecord($data);
    }

    public function newRecordSet($class)
    {
        return $this->mapper($class)->newRecordSet();
    }

    public function fetchRecord($class, $primaryVal, array $with = [])
    {
        return $this->mapper($class)->fetchRecord($primaryVal, $with);
    }

    public function fetchRecordBy($class, array $colsVals, array $with = [])
    {
        return $this->mapper($class)->fetchRecordBy($colsVals, $with);
    }

    public function fetchRecordSet($class, array $primaryVals, array $with = [])
    {
        return $this->mapper($class)->fetchRecordSet($primaryVals, $with);
    }

    public function fetchRecordSetBy($class, array $colsVals, array $with = [])
    {
        return $this->mapper($class)->fetchRecordSetBy($colsVals, $with);
    }

    public function select($class, array $colsVals = [])
    {
        return $this->mapper($class)->select($colsVals);
    }

    public function insert(AbstractRecord $record)
    {
        return $this->transact('insert', $record);
    }

    public function update(AbstractRecord $record)
    {
        return $this->transact('update', $record);
    }

    public function delete(AbstractRecord $record)
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
