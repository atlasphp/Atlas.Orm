<?php
namespace Atlas;

// work to be performed inside a transaction
class Work
{
    protected $label;
    protected $callable;
    protected $args;
    protected $result;
    protected $invoked = false;

    public function __construct($label, callable $callable, array $args)
    {
        $this->label = $label;
        $this->callable = $callable;
        $this->args = $args;
    }

    public function __invoke()
    {
        if ($this->invoked) {
            throw Exception::priorWork();
        }

        $this->invoked = true;
        $this->result = call_user_func_array($this->callable, $this->args);
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function getCallable()
    {
        return $this->callable;
    }

    public function getArgs()
    {
        return $this->args;
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
