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
        return new $mapperClass(
            $this->atlasContainer->getConnectionLocator(),
            $this->atlasContainer->getQueryFactory(),
            $this->atlasContainer->getIdentityMap(),
            $this->atlasContainer->newInstance($this->tableClass),
            $this->atlasContainer->newInstance($mapperClass . 'Events'),
            $this->atlasContainer->newMapperRelations()
        );
    }
}
