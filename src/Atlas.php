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

    public function mapper($class)
    {
        return $this->mapperLocator->get($class);
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
