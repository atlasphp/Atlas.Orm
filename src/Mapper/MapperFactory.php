<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\AtlasContainer;

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
        $type = substr($mapperClass, 0, -6);

        return new $mapperClass(
            $this->atlasContainer->getGateway($this->tableClass),
            $this->atlasContainer->newInstance("{$type}RecordFactory"),
            $this->atlasContainer->newInstance("{$type}MapperEvents"),
            new MapperRelations($this->atlasContainer->getMapperLocator())
        );
    }
}
