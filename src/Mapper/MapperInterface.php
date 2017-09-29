<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Table\TableInterface;
use Atlas\Orm\Table\RowInterface;
use SplObjectStorage;

/**
 *
 * Interface for a data source mapper that returns Record and RecordSet objects.
 *
 * @package atlas/orm
 *
 */
interface MapperInterface
{
    /**
     *
     * Returns the name of the Table class to use when constructing the mapper.
     *
     * @return string
     *
     */
    static public function getTableClass() : string;

    /**
     *
     * Returns the underlying Table object.
     *
     * @return TableInterface
     *
     */
    public function getTable() : TableInterface;

    /**
     *
     * Fetches one Record by its primary key value, optionally with relateds.
     *
     * @param mixed $primaryVal The primary key value; a scalar in the case of
     * simple keys, or an array of key-value pairs for composite keys.
     *
     * @param array $with Return the Record with these relateds stitched in.
     *
     * @return RecordInterface|false A Record on success, or `false` on failure.
     * (If a Mapper-specific Record class is defined, that will be returned on
     * success instead of a generic Record.)
     *
     */
    public function fetchRecord($primaryVal, array $with = []) : ?RecordInterface;

    /**
     *
     * Fetches one Record by column-value equality pairs, optionally with
     * relateds.
     *
     * @param array $whereEquals The column-value equality pairs.
     *
     * @param array $with Return the Record with these relateds stitched in.
     *
     * @return RecordInterface|false A Record on success, or `false` on failure.
     * (If a Mapper-specific Record class is defined, that will be returned on
     * success instead of a generic Record.)
     *
     */
    public function fetchRecordBy(array $whereEquals, array $with = []) : ?RecordInterface;

    /**
     *
     * Fetches an array of Records by primary key values, optionally with relateds.
     *
     * @param array $primaryVals The primary key values. Each element in the
     * array is a scalar in the case of simple keys, or an array of key-value
     * pairs for composite keys.
     *
     * @param array $with Return each Record with these relateds stitched in.
     *
     * @return array An array of Records.
     *
     */
    public function fetchRecords(array $primaryVals, array $with = []) : array;

    /**
     *
     * Fetches an array of Records by column-value equality pairs, optionally with
     * relateds.
     *
     * @param array $whereEquals The column-value equality pairs.
     *
     * @param array $with Return each Record with these relateds stitched in.
     *
     * @return array An array of Records.
     *
     */
    public function fetchRecordsBy(array $whereEquals, array $with = []) : array;

    /**
     *
     * Fetches a RecordSet by primary key values, optionally with relateds.
     *
     * @param array $primaryVals The primary key values. Each element in the
     * array is a scalar in the case of simple keys, or an array of key-value
     * pairs for composite keys.
     *
     * @param array $with Return each Record with these relateds stitched in.
     *
     * @return RecordSetInterface
     *
     */
    public function fetchRecordSet(array $primaryVals, array $with = []) : RecordSetInterface;

    /**
     *
     * Fetches a RecordSet by column-value equality pairs, optionally with
     * relateds.
     *
     * @param array $whereEquals The column-value equality pairs.
     *
     * @param array $with Return each Record with these relateds stitched in.
     *
     * @return RecordSetInterface
     *
     */
    public function fetchRecordSetBy(array $whereEquals, array $with = []) : RecordSetInterface;

    /**
     *
     * Returns a new MapperSelect object.
     *
     * @param array $whereEquals A series of column-value equality pairs for the
     * WHERE clause.
     *
     * @return MapperSelect
     *
     */
    public function select(array $whereEquals = []) : MapperSelect;

    /**
     *
     * Inserts the Row for a Record.
     *
     * @param RecordInterface $record Insert the Row for this Record.
     *
     * @return bool
     *
     */
    public function insert(RecordInterface $record) : bool;

    /**
     *
     * Updates the Row for a Record.
     *
     * @param RecordInterface $record Update the Row for this Record.
     *
     * @return bool
     *
     */
    public function update(RecordInterface $record) : bool;

    /**
     *
     * Deletes the Row for a Record.
     *
     * @param RecordInterface $record Delete the Row for this Record.
     *
     * @return bool
     *
     */
    public function delete(RecordInterface $record) : bool;

    /**
     *
     * Persists a Record and its relateds to the database.
     *
     * @param RecordInterface $record Persist this Record and its relateds.
     *
     * @param SplObjectStorage $tracker Tracks which Records have been
     * persisted, to avoid infinite recursion.
     *
     * @return bool
     *
     */
    public function persist(RecordInterface $record, SplObjectStorage $tracker = null) : bool;

    /**
     *
     * Returns a new Record object.
     *
     * @param array $cols Populate the underlying Row fields with these values.
     *
     * @return RecordInterface If a Mapper-specific Record class is defined,
     * that will be returned instead of a generic Record.
     *
     */
    public function newRecord(array $cols = []) : RecordInterface;

    /**
     *
     * Returns a new RecordSet object.
     *
     * @param array $records Populate RecordSet with these Record objects.
     *
     * @return RecordSetInterface If a Mapper-specific RecordSet class is
     * defined, that will be returned instead of a generic RecordSet.
     *
     */
    public function newRecordSet(array $records = []) : RecordSetInterface;

    /**
     *
     * Given a Row, return a new Record, optionally with relateds.
     *
     * @param RowInterface $row A selected Row.
     *
     * @param array $with Return the Record with these relateds stitched in.
     *
     * @return RecordInterface If a Mapper-specific Record class is defined,
     * that will be returned instead of a generic Record.
     *
     */
    public function turnRowIntoRecord(RowInterface $row, array $with = []) : RecordInterface;

    /**
     *
     * Given an array of Row objects, return an array of Record objects,
     * optionally with relateds. Note that this is an *array of Record objects*
     * and not a RecordSet.
     *
     * @param array $rows An array of selected Row objects.
     *
     * @param array $with Return each Record with these relateds stitched in.
     *
     * @return array An array of Record objects. If a Mapper-specific Record
     * class is defined, those will be returned in the array instead of generic
     * Record objects.
     *
     */
    public function turnRowsIntoRecords(array $rows, array $with = []) : array;
}
