<?php
namespace Atlas;

use Atlas\Mapper\MapperLocator;
use Atlas\Mapper\MapperInterface;
use Atlas\Table\Select as TableSelect;

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

    public function mapper($type)
    {
        if (is_object($type)) {
            $type = get_class($type);
        }
        return $this->mapperLocator->get($type);
    }

    public function fetchRecord($type, $primaryVal, array $with = [])
    {
        $mapper = $this->mapper($type);
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

    public function fetchRecordSet($type, $primaryVals, array $with = [])
    {
        $mapper = $this->mapper($type);
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

    public function select($type, array $colsVals = [])
    {
        $mapper = $this->mapper($type);
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
