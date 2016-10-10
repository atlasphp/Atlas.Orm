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

/**
 *
 * A data source mapper that returns Record and RecordSet objects.
 *
 * @package atlas/orm
 *
 */
interface MapperInterface
{
    static public function getTableClass();

    public function getTable();

    public function fetchRecord($primaryVal, array $with = []);

    public function fetchRecordBy(array $colsVals, array $with = []);

    public function fetchRecordSet(array $primaryVals, array $with = []);

    public function fetchRecordSetBy(array $colsVals, array $with = []);

    public function select(array $colsVals = []);

    public function insert(RecordInterface $record);

    public function update(RecordInterface $record);

    public function delete(RecordInterface $record);

    public function newRecord(array $cols = []);

    public function newRecordSet(array $records = []);

    public function getSelectedRecord(array $cols, array $with = []);

    public function getSelectedRecordSet(array $data, array $with = []);
}
