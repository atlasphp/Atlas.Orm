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
    /**
     *
     * A descriptive label for the work.
     *
     * @var string
     *
     */
    protected $label;

    /**
     *
     * A callable to perform the work.
     *
     * @var callable
     *
     */
    protected $callable;

    /**
     *
     * The record being worked with.
     *
     * @var RecordInterface
     *
     */
    protected $record;

    /**
     *
     * The result of the work.
     *
     * @var bool
     *
     */
    protected $result;

    /**
     *
     * Has the work callable already been invoked?
     *
     * @var bool
     *
     */
    protected $invoked = false;

    /**
     *
     * Constructor.
     *
     * @param string $label A descriptive label for the work.
     *
     * @param callable $callable A callable to perform the work.
     *
     * @param RecordInterface $record The record being worked with.
     *
     */
    public function __construct(string $label, callable $callable, RecordInterface $record)
    {
        $this->label = $label;
        $this->callable = $callable;
        $this->record = $record;
    }

    /**
     *
     * Performs the work.
     *
     * @throws Exception if it has already been performed.
     *
     */
    public function __invoke() : void
    {
        if ($this->invoked) {
            throw Exception::priorWork();
        }

        $this->invoked = true;
        $this->result = call_user_func($this->callable, $this->record);
    }

    /**
     *
     * Returns the descriptive label.
     *
     * @return string
     *
     */
    public function getLabel() : string
    {
        return $this->label;
    }

    /**
     *
     * Returns the callable to perform the work.
     *
     * @return callable
     *
     */
    public function getCallable() : callable
    {
        return $this->callable;
    }

    /**
     *
     * Returns the record being worked with.
     *
     * @return RecordInterface
     *
     */
    public function getRecord() : RecordInterface
    {
        return $this->record;
    }

    /**
     *
     * Returns the result of the work.
     *
     * @return ?bool
     *
     */
    public function getResult() : ?bool
    {
        return $this->result;
    }

    /**
     *
     * Returns the "invoked" status (i.e., has the work callable already been invoked).
     *
     * @return bool
     *
     */
    public function getInvoked() : bool
    {
        return $this->invoked;
    }
}
