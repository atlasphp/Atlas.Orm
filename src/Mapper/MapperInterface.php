<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Mapper;

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
    static public function getTableClass();

    /**
     *
     * Returns the underlying Table object.
     *
     * @return TableInterface
     *
     */
    public function getTable();

    /**
     *
     * Returns the Table read connection.
     *
     * @return ExtendedPdoInterface
     *
     */
    public function getReadConnection();

    /**
     *
     * Returns the Table write connection.
     *
     * @return ExtendedPdoInterface
     *
     */
    public function getWriteConnection();

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
    public function fetchRecord($primaryVal, array $with = []);

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
    public function fetchRecordBy(array $whereEquals, array $with = []);

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
     * @return RecordSetInterface|array A RecordSet on success, or an empty
     * array on failure. (If a mapper-specific RecordSet class is defined, that
     * will be returned instead of a generic RecordSet.)
     *
     */
    public function fetchRecordSet(array $primaryVals, array $with = []);

    /**
     *
     * Fetches a RecordSet by column-value equality pairs, optionally with
     * relateds.
     *
     * @param array $whereEquals The column-value equality pairs.
     *
     * @param array $with Return each Record with these relateds stitched in.
     *
     * @return RecordSetInterface|array A RecordSet on success, or an empty
     * array on failure. (If a mapper-specific RecordSet class is defined, that
     * will be returned instead of a generic RecordSet.)
     *
     */
    public function fetchRecordSetBy(array $whereEquals, array $with = []);

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
    public function select(array $whereEquals = []);

    /**
     *
     * Inserts the Row for a Record.
     *
     * @param RecordInterface $record Insert the Row for this Record.
     *
     * @return mixed
     *
     */
    public function insert(RecordInterface $record);

    /**
     *
     * Updates the Row for a Record.
     *
     * @param RecordInterface $record Update the Row for this Record.
     *
     * @return mixed
     *
     */
    public function update(RecordInterface $record);

    /**
     *
     * Deletes the Row for a Record.
     *
     * @param RecordInterface $record Delete the Row for this Record.
     *
     * @return mixed
     *
     */
    public function delete(RecordInterface $record);

    /**
     *
     * Persists a Record and its relateds to the database.
     *
     * @param RecordInterface $record Persist this Record and its relateds.
     *
     * @param SplObjectStorage $tracker Tracks which Records have been
     * persisted, to avoid infinite recursion.
     *
     * @return mixed
     *
     */
    public function persist(RecordInterface $record, SplObjectStorage $tracker = null);

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
    public function newRecord(array $cols = []);

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
    public function newRecordSet(array $records = []);

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
    public function turnRowIntoRecord(RowInterface $row, array $with = []);

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
    public function turnRowsIntoRecords(array $rows, array $with = []);

    /**
     *
     * Given an array of Row objects, returns a RecordSet object,
     * optionally with relateds.
     *
     * @param array $rows An array of selected Row objects.
     *
     * @param array $with Return each Record with these relateds stitched in.
     *
     * @return RecordSet If a Mapper-specific RecordSet class is defined, that
     * will be returned of a generic RecordSet object.
     *
     */
    public function turnRowsIntoRecordSet(array $rows, array $with = []);
}
