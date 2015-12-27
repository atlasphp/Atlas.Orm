<?php
namespace Atlas\Orm\Mapper;

interface RecordInterface
{
    public function __get($field);

    public function __set($field, $value);

    public function __isset($field);

    public function __unset($field);

    public function getMapperClass();

    public function has($field);

    public function getRow();

    public function getRelated();

    public function getArrayCopy();
}
