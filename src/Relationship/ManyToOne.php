<?php
namespace Atlas\Orm\Relationship;

class ManyToOne extends OneToOne
{
    protected function fixOn()
    {
        if ($this->on) {
            return;
        }

        foreach ($this->foreignMapper->getTable()->getPrimaryKey() as $col) {
            $this->on[$col] = $col;
        }
    }
}
