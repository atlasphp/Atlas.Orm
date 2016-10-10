<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Relationship;

/**
 *
 * Defines a one-to-one relationship.
 *
 * @package atlas/orm
 *
 */
class ManyToOne extends OneToOne
{
    /**
     * @inheritdoc
     */
    protected function initializeOn()
    {
        foreach ($this->foreignMapper->getTable()->getPrimaryKey() as $col) {
            $this->on[$col] = $col;
        }
    }
}
