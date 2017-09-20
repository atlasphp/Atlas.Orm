<?php
namespace Atlas\Orm\Fake;

class CallableWithObject
{
    public function replies($query)
    {
        $query->with(['author']);
    }
}
