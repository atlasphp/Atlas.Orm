<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\AtlasContainer;

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
        $type = substr($tableClass, 0, -5);
        return new $tableClass(
            $this->atlasContainer->getConnectionLocator(),
            $this->atlasContainer->getQueryFactory(),
            $this->atlasContainer->getIdentityMap(),
            $this->atlasContainer->newInstance("{$type}RowFactory"),
            $this->atlasContainer->newInstance("{$type}RowFilter")
        );
    }
}
