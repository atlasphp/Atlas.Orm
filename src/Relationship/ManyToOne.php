<?php
namespace Atlas\Relationship;

use Atlas\Mapper\MapperLocator;

class ManyToOne extends OneToOne
{
    protected function fixNativeCol()
    {
        if ($this->nativeCol) {
            return;
        }

        $foreignMapper = $this->mapperLocator->get($this->foreignMapperClass);
        $this->nativeCol = $foreignMapper->getTable()->getPrimary();
    }

    protected function fixForeignCol()
    {
        if ($this->foreignCol) {
            return;
        }

        $foreignMapper = $this->mapperLocator->get($this->foreignMapperClass);
        $this->foreignCol = $foreignMapper->getTable()->getPrimary();
    }
}
