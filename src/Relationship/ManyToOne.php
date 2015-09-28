<?php
namespace Atlas\Relationship;

use Atlas\Mapper\MapperLocator;

class ManyToOne extends OneToOne
{
    protected function fixNativeCol(MapperLocator $mapperLocator)
    {
        if ($this->nativeCol) {
            return;
        }

        $foreignMapper = $mapperLocator->get($this->foreignMapperClass);
        $this->nativeCol = $foreignMapper->getTable()->getPrimary();
    }

    protected function fixForeignCol(MapperLocator $mapperLocator)
    {
        if ($this->foreignCol) {
            return;
        }

        $foreignMapper = $mapperLocator->get($this->foreignMapperClass);
        $this->foreignCol = $foreignMapper->getTable()->getPrimary();
    }
}
