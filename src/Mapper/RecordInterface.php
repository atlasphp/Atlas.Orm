<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @package atlas/orm
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Mapper;

interface RecordInterface
{
    public function getMapperClass();

    public function has($field);

    public function getRow();

    public function getRelated();

    public function getArrayCopy();
}
