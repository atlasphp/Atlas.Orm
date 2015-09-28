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

    protected function getMapperClass($spec)
    {
        if (is_object($spec)) {
            $spec = get_class($spec);
        }

        if (substr($spec, -6) == 'Record') {
            $spec = substr($spec, 0, -6) . 'Mapper';
        }

        if (substr($spec, -9) == 'RecordSet') {
            $spec = substr($spec, 0, -9) . 'Mapper';
        }

        return $spec;
    }

    public function mapper($spec)
    {
        $mapperClass = $this->getMapperClass($spec);
        return $this->mapperLocator->get($mapperClass);
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
