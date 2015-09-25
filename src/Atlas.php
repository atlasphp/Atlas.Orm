<?php
namespace Atlas;

use Atlas\Mapper\Mapper;
use Atlas\Mapper\MapperLocator;
use Atlas\Table\TableSelect;

class Atlas
{
    protected $mapperLocator;

    public function __construct(MapperLocator $mapperLocator)
    {
        $this->mapperLocator = $mapperLocator;
    }

    public function getMapperLocator()
    {
        return $this->mapperLocator;
    }

    public function mapper($mapperClass)
    {
        return $this->mapperLocator->get($mapperClass);
    }

    public function fetchRecord($mapperClass, $primaryVal, array $with = [])
    {
        $mapper = $this->mapper($mapperClass);
        $record = $mapper->fetchRecord($primaryVal);
        if ($record) {
            $mapper->getRelations()->stitchIntoRecord(
                $this,
                $record,
                $with
            );
        }
        return $record;
    }

    public function fetchRecordSet($mapperClass, $primaryVals, array $with = [])
    {
        $mapper = $this->mapper($mapperClass);
        $recordSet = $mapper->fetchRecordSet($primaryVals);
        if ($recordSet) {
            $mapper->getRelations()->stitchIntoRecordSet(
                $this,
                $recordSet,
                $with
            );
        }
        return $recordSet;
    }

    public function select($mapperClass, array $colsVals = [])
    {
        $mapper = $this->mapper($mapperClass);
        return $this->newSelect($mapper, $mapper->select($colsVals));
    }

    public function insert($entity)
    {
        return $this->mapper($entity)->insert($entity);
    }

    public function update($entity)
    {
        return $this->mapper($entity)->update($entity);
    }

    public function delete($entity)
    {
        return $this->mapper($entity)->delete($entity);
    }

    protected function newSelect(Mapper $mapper, TableSelect $select)
    {
        return new AtlasSelect($this, $mapper, $select);
    }
}
