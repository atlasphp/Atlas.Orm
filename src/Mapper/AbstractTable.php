<?php
namespace Atlas\Orm\Mapper;

abstract class AbstractTable implements TableInterface
{
    public function hasCol($name)
    {
        return in_array($name, $this->getCols());
    }
}
