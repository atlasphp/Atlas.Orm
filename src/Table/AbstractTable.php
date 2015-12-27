<?php
namespace Atlas\Orm\Table;

abstract class AbstractTable implements TableInterface
{
    public function hasCol($name)
    {
        return in_array($name, $this->getCols());
    }
}
