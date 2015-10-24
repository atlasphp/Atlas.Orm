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
        $recordFactoryClass = substr($mapperClass, 0, -6) . 'RecordFactory';
        return new $mapperClass(
            $this->atlasContainer->getTable($this->tableClass),
            $this->atlasContainer->newInstance($recordFactoryClass),
            $this->atlasContainer->newMapperRelations($mapperClass)
        );
    }
}
