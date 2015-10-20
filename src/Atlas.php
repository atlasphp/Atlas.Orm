<?php
namespace Atlas;

use Atlas\Mapper\MapperLocator;
use Atlas\Mapper\Record;
use Atlas\Table\TableSelect;

class Atlas
{
    protected $mapperLocator;

    public function __construct(MapperLocator $mapperLocator)
    {
        $this->mapperLocator = $mapperLocator;
    }

    public function mapper($class)
    {
        return $this->mapperLocator->get($class);
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

    public function insert(Record $record)
    {
        return $this->mapper($record)->insert($record);
    }

    public function update(Record $record)
    {
        return $this->mapper($record)->update($record);
    }

    public function delete(Record $record)
    {
        return $this->mapper($record)->delete($record);
    }
}
