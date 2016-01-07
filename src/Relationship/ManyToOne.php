<?php
namespace Atlas\Orm\Relationship;

class ManyToOne extends OneToOne
{
    protected function fixNativeCol()
    {
        if ($this->nativeCol) {
            return;
        }

        $primaryKey = (array) $this->foreignMapper->getTable()->getPrimaryKey();
        $primaryCol = $primaryKey[0];
        $this->nativeCol($primaryCol);
    }

    protected function fixForeignCol()
    {
        if ($this->foreignCol) {
            return;
        }

        $primaryKey = (array) $this->foreignMapper->getTable()->getPrimaryKey();
        $primaryCol = $primaryKey[0];
        $this->foreignCol($primaryCol);
    }
}
