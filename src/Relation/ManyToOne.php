<?php
namespace Atlas\Orm\Relation;

use Atlas\Orm\Mapper\MapperLocator;

class ManyToOne extends OneToOne
{
    protected function fixNativeCol()
    {
        if ($this->nativeCol) {
            return;
        }

        $this->nativeCol($this->foreignMapper->getGateway()->tablePrimary());
    }

    protected function fixForeignCol()
    {
        if ($this->foreignCol) {
            return;
        }

        $this->foreignCol($this->foreignMapper->getGateway()->tablePrimary());
    }
}
