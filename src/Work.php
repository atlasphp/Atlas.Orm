<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm;

use Atlas\Orm\Mapper\RecordInterface;

/**
 *
 * Work to be performed inside a transaction.
 *
 * @package atlas/orm
 *
 */
class Work
{
    protected $label;
    protected $callable;
    protected $record;
    protected $result;
    protected $invoked = false;

    public function __construct($label, callable $callable, RecordInterface $record)
    {
        $this->label = $label;
        $this->callable = $callable;
        $this->record = $record;
    }

    public function __invoke()
    {
        if ($this->invoked) {
            throw Exception::priorWork();
        }

        $this->invoked = true;
        $this->result = call_user_func($this->callable, $this->record);
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getCallable()
    {
        return $this->callable;
    }

    public function getRecord()
    {
        return $this->record;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getInvoked()
    {
        return $this->invoked;
    }
}
