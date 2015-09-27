<?php
namespace Atlas\Mapper;

use Atlas\AtlasContainer;

class MapperFactory
{
    public function __construct(
        AtlasContainer $atlasContainer,
        $mapperClass,
        $tableClass
    ) {
        $this->atlasContainer = $atlasContainer;
        $this->mapperClass = $mapperClass;
        $this->tableClass = $tableClass;
    }

    public function __invoke()
    {
        $mapperClass = $this->mapperClass;
        $table = $this->atlasContainer->getTable($this->tableClass);
        $relations = new Relations($mapperClass);
        return new $mapperClass($table, $relations);
    }
}
