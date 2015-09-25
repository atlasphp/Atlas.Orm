<?php
namespace Atlas\Table;

use Atlas\AtlasContainer;

class TableFactory
{
    protected $atlasContainer;
    protected $tableClass;

    public function __construct(AtlasContainer $atlasContainer, $tableClass)
    {
        $this->atlasContainer = $atlasContainer;
        $this->tableClass = $tableClass;
    }

    public function __invoke()
    {
        $tableClass = $this->tableClass;
        $rowFilterClass = substr($tableClass, 0, -5) . 'RowFilter';
        return new $tableClass(
            $this->atlasContainer->getConnectionLocator(),
            $this->atlasContainer->getQueryFactory(),
            $this->atlasContainer->invokeFactoryFor(IdentityMap::CLASS),
            $this->atlasContainer->invokeFactoryFor($rowFilterClass)
        );
    }
}
