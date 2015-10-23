<?php
namespace Atlas\Relation;

use Atlas\Mapper\MapperLocator;

class BelongsTo extends HasOne
{
    protected function fixNativeCol()
    {
        if ($this->nativeCol) {
            return;
        }

        $this->nativeCol($this->foreignMapper->getTable()->getPrimary());
    }

    protected function fixForeignCol()
    {
        if ($this->foreignCol) {
            return;
        }

        $this->foreignCol($this->foreignMapper->getTable()->getPrimary());
    }
}
