<?php
namespace Atlas\Orm\Relationship;

class ManyToOne extends OneToOne
{
    protected function fixNativeKey()
    {
        if ($this->nativeKey) {
            return;
        }

        $primaryKey = $this->foreignMapper->getTable()->getPrimaryKey();
        $primaryCol = $primaryKey[0];
        $this->nativeKey($primaryCol);
    }

    protected function fixForeignKey()
    {
        if ($this->foreignKey) {
            return;
        }

        $primaryKey = $this->foreignMapper->getTable()->getPrimaryKey();
        $primaryCol = $primaryKey[0];
        $this->foreignKey($primaryCol);
    }
}
