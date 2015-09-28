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

        $this->nativeCol = $this->foreignMapper->getTable()->getPrimary();
    }

    protected function fixForeignCol()
    {
        if ($this->foreignCol) {
            return;
        }

        $this->foreignCol = $this->foreignMapper->getTable()->getPrimary();
    }
}
