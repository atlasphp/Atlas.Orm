<?php
namespace Atlas;

use Atlas\Mapper\Mapper;
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

    public function getMapperLocator()
    {
        return $this->mapperLocator;
    }

    public function mapper($spec)
    {
        $mapperClass = $this->getMapperClass($spec);
        return $this->mapperLocator->get($mapperClass);
    }

    protected function getMapperClass($spec)
    {
        if (is_object($spec)) {
            $spec = get_class($spec);
        }

        if (substr($spec, -6) == 'Record') {
            $spec = substr($spec, 0, -6) . 'Mapper';
        }

        return $spec;
    }

    public function fetchRecord($spec, $primaryVal, array $with = [])
    {
        $mapper = $this->mapper($spec);
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

    public function fetchRecordSet($spec, $primaryVals, array $with = [])
    {
        $mapper = $this->mapper($spec);
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

    public function select($spec, array $colsVals = [], callable $custom = null)
    {
        $mapper = $this->mapper($spec);
        $select = $this->newAtlasSelect($mapper, $mapper->select($colsVals));
        if ($custom) {
            $custom($select);
        }
        return $select;
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

    protected function newAtlasSelect(Mapper $mapper, TableSelect $select)
    {
        return new AtlasSelect($this, $mapper, $select);
    }
}
