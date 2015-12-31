<?php
namespace Atlas\Orm\Mapper;

interface RecordInterface
{
    public function getMapperClass();

    public function has($field);

    public function getRow();

    public function getRelated();

    public function getArrayCopy();
}
