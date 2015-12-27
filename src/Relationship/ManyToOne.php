<?php
namespace Atlas\Orm\Relationship;

class ManyToOne extends OneToOne
{
    protected function fixNativeCol()
    {
        if ($this->nativeCol) {
            return;
        }

        $this->nativeCol($this->foreignMapper->getTable()->getPrimaryKey());
    }

    protected function fixForeignCol()
    {
        if ($this->foreignCol) {
            return;
        }

        $this->foreignCol($this->foreignMapper->getTable()->getPrimaryKey());
    }
}
