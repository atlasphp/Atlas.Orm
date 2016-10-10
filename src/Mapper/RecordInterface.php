<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Mapper;

/**
 *
 * __________
 *
 * @package atlas/orm
 *
 */
interface RecordInterface
{
    public function getMapperClass();

    public function has($field);

    public function getRow();

    public function getRelated();

    public function getArrayCopy();
}
