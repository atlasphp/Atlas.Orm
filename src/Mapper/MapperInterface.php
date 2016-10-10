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
     * @param array $colsVals The column-value equality pairs.
     *
     * @return RecordInterface|false A Record on success, or `false` on failure.
     * (If a Mapper-specific Record class is defined, that will be returned on
     * success instead of a generic Record.)
     *
     */
    public function fetchRecordBy(array $colsVals, array $with = []);

    /**
     *
     * Fetches a RecordSet by primary key values, optionally with relateds.
     *
     * @param array $primaryVal The primary key values. Each element in the
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
     * @param array $colsVals The column-value equality pairs.
     *
     * @param array $with Return each Record with these relateds stitched in.
     *
     * @return RecordSetInterface|array A RecordSet on success, or an empty
     * array on failure. (If a mapper-specific RecordSet class is defined, that
     * will be returned instead of a generic RecordSet.)
     *
     */
    public function fetchRecordSetBy(array $colsVals, array $with = []);

    /**
     *
     * Returns a new MapperSelect object.
     *
     * @param array $colsVals A series of column-value equality pairs for the
     * WHERE clause.
     *
     * @return MapperSelect
     *
     */
    public function select(array $colsVals = []);

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
     * Given an array of selected column data, return a new Record, optionally
     * with relateds.
     *
     * @param array $cols An array of selected column data for a Row.
     *
     * @param array $with Return the Record with these relateds stitched in.
     *
     * @return RecordInterface If a Mapper-specific Record class is defined,
     * that will be returned instead of a generic Record.
     *
     */
    public function getSelectedRecord(array $cols, array $with = []);

    /**
     *
     * Given an array of selected row data, return an array of Record objects,
     * optionally with relateds. Note that this is an *array of Record objects*
     * and not a RecordSet. Generally used only by the MapperSelect class.
     *
     * @param array $data An array of selected data for Record objects.
     *
     * @param array $with Return each Record with these relateds stitched in.
     *
     * @return array An array of Record objects. If a Mapper-specific Record
     * class is defined, those will be returned in the array instead of generic
     * Record objects.
     *
     */
    public function getSelectedRecords(array $data, array $with = []);

    /**
     *
     * Given an array of selected row data, returns a RecordSet object,
     * optionally with relateds. Generally used only by the MapperSelect class.
     *
     * @param array $data An array of selected data for Record objects.
     *
     * @param array $with Return each Record with these relateds stitched in.
     *
     * @return RecordSet If a Mapper-specific RecordSet class is defined, that
     * will be returned of a generic RecordSet object.
     *
     */
    public function getSelectedRecordSet(array $data, array $with = []);
}
