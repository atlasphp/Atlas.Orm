<?php
namespace Atlas\Relationship;

use Atlas\Atlas;
use Atlas\Mapper\Record;
use Atlas\Mapper\RecordSet;

class ManyToOne extends OneToOne
{
    protected function fixNativeCol(Atlas $atlas)
    {
        if ($this->nativeCol) {
            return;
        }

        $foreignMapper = $atlas->mapper($this->foreignMapperClass);
        $this->nativeCol = $foreignMapper->getTable()->getPrimary();
    }

    protected function fixForeignCol(Atlas $atlas)
    {
        if ($this->foreignCol) {
            return;
        }

        $foreignMapper = $atlas->mapper($this->foreignMapperClass);
        $this->foreignCol = $foreignMapper->getTable()->getPrimary();
    }
}
