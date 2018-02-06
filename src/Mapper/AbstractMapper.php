<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Exception;
use Atlas\Orm\Relationship\RelationshipInterface;
use Atlas\Orm\Relationship\Relationships;
use Atlas\Orm\Table\RowInterface;
use Atlas\Orm\Table\TableInterface;
use Aura\Sql\ExtendedPdoInterface;
use SplObjectStorage;

/**
 *
 * A data source mapper that returns Record and RecordSet objects.
 *
 * @package atlas/orm
 *
 */
abstract class AbstractMapper implements MapperInterface
{
    /**
     *
     * The underlying table object for this mapper.
     *
     * @var TableInterface
     *
     */
    protected $table;

    /**
     *
     * The relationships to other Mapper objects.
     *
     * @var Relationships
     *
     */
    protected $relationships;

    /**
     *
     * Events to invoke during Mapper operations.
     *
     * @var MapperEventsInterface
     *
     */
    protected $events;

    /**
     *
     * Constructor.
     *
     * @param TableInterface $table The underlying table object for this mapper.
     *
     * @param Relationships $relationships The relationships to other mappers.
     *
     * @param MapperEventsInterface $events Events to invoke during mapper
     * operations.
     *
     */
    public function __construct(
        TableInterface $table,
        Relationships $relationships,
        MapperEventsInterface $events
    ) {
        $this->table = $table;
        $this->relationships = $relationships;
        $this->events = $events;
        $this->setRelated();
    }

    /**
     *
     * Returns the name of the Table class to use when constructing the Mapper.
     *
     * By default, it is the same name as the mapper class, but suffixed with
     * 'Table' instead of 'Mapper'.
     *
     * @return string
     *
     */
    static public function getTableClass() : string
    {
        static $tableClass;
        if (! $tableClass) {
            $tableClass = substr(get_called_class(), 0, -6) . 'Table';
        }
        return $tableClass;
    }

    /**
     *
     * Returns the underlying Table object.
     *
     * @return TableInterface
     *
     */
    public function getTable() : TableInterface
    {
        return $this->table;
    }

    /**
     *
     * Returns the Table read connection.
     *
     * @return ExtendedPdoInterface
     *
     */
    public function getReadConnection() : ExtendedPdoInterface
    {
        return $this->table->getReadConnection();
    }

    /**
     *
     * Returns the Table write connection.
     *
     * @return ExtendedPdoInterface
     *
     */
    public function getWriteConnection() : ExtendedPdoInterface
    {
        return $this->table->getWriteConnection();
    }

    /**
     *
     * Returns the relationships to other Mapper objects.
     *
     * @return Relationships
     *
     */
    public function getRelationships() : Relationships
    {
        return $this->relationships;
    }

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
    public function fetchRecord($primaryVal, array $with = []) : ?RecordInterface
    {
        $row = $this->table->fetchRow($primaryVal);
        if (! $row) {
            return null;
        }

        return $this->turnRowIntoRecord($row, $with);
    }

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
    public function fetchRecordBy(array $whereEquals, array $with = []) : ?RecordInterface
    {
        $row = $this->table->select($whereEquals)->fetchRow();
        if (! $row) {
            return null;
        }

        return $this->turnRowIntoRecord($row, $with);
    }

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
    public function fetchRecords(array $primaryVals, array $with = []) : array
    {
        $rows = $this->table->fetchRows($primaryVals);
        return $this->turnRowsIntoRecords($rows, $with);
    }

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
    public function fetchRecordsBy(array $whereEquals, array $with = []) : array
    {
        $rows = $this->table->select($whereEquals)->fetchRows();
        return $this->turnRowsIntoRecords($rows, $with);
    }

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
    public function fetchRecordSet(array $primaryVals, array $with = []) : RecordSetInterface
    {
        $records = $this->fetchRecords($primaryVals, $with);
        return $this->newRecordSet($records);
    }

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
    public function fetchRecordSetBy(array $whereEquals, array $with = []) : RecordSetInterface
    {
        $records = $this->fetchRecordsBy($whereEquals, $with);
        return $this->newRecordSet($records);
    }

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
    public function select(array $whereEquals = []) : MapperSelect
    {
        return new MapperSelect(
            $this,
            $this->table->select($whereEquals)
        );
    }

    /**
     *
     * Inserts the Row for a Record.
     *
     * @param RecordInterface $record Insert the Row for this Record.
     *
     * @return bool
     *
     */
    public function insert(RecordInterface $record) : bool
    {
        $this->events->beforeInsert($this, $record);
        $this->relationships->fixNativeRecordKeys($record);
        $insert = $this->table->insertRowPrepare($record->getRow());
        $this->events->modifyInsert($this, $record, $insert);
        $pdoStatement = $this->table->insertRowPerform($record->getRow(), $insert);
        $this->relationships->fixForeignRecordKeys($record);
        $this->events->afterInsert($this, $record, $insert, $pdoStatement);
        return true;
    }

    /**
     *
     * Updates the Row for a Record.
     *
     * @param RecordInterface $record Update the Row for this Record.
     *
     * @return bool
     *
     */
    public function update(RecordInterface $record) : bool
    {
        $this->events->beforeUpdate($this, $record);
        $this->relationships->fixNativeRecordKeys($record);
        $update = $this->table->updateRowPrepare($record->getRow());
        $this->events->modifyUpdate($this, $record, $update);
        $pdoStatement = $this->table->updateRowPerform($record->getRow(), $update);
        $this->relationships->fixForeignRecordKeys($record);
        if (! $pdoStatement) {
            return false;
        }
        $this->events->afterUpdate($this, $record, $update, $pdoStatement);
        return true;
    }

    /**
     *
     * Deletes the Row for a Record.
     *
     * @param RecordInterface $record Delete the Row for this Record.
     *
     * @return mixed
     *
     */
    public function delete(RecordInterface $record) : bool
    {
        $this->events->beforeDelete($this, $record);
        $delete = $this->table->deleteRowPrepare($record->getRow());
        $this->events->modifyDelete($this, $record, $delete);
        $pdoStatement = $this->table->deleteRowPerform($record->getRow(), $delete);
        $this->events->afterDelete($this, $record, $delete, $pdoStatement);
        return true;
    }

    /**
     *
     * Persists a Record and its relateds to the database.
     *
     * This method will:
     *
     * - insert the Row for the Record if it is new;
     * - update the Row for the Record if it has been modified; or,
     * - delete the Row for the Record if the Record is marked for deletion.
     *
     * Whether or not the Row for the Record is inserted/updated/deleted, this
     * method will *also* recursively traverse all the related fields and
     * persist them as well.
     *
     * @param RecordInterface $record Persist this Record and its relateds.
     *
     * @param SplObjectStorage $tracker Tracks which Records have been
     * persisted, to avoid infinite recursion.
     *
     * @return bool
     *
     */
    public function persist(RecordInterface $record, SplObjectStorage $tracker = null) : bool
    {
        if ($tracker === null) {
            $tracker = new SplObjectStorage();
        }

        if ($tracker->contains($record)) {
            return false;
        }

        $tracker->attach($record);

        $this->relationships->persistBeforeNative($record, $tracker);
        $this->relationships->fixNativeRecordKeys($record);

        $method = $record->getPersistMethod();
        if ($method) {
            $this->$method($record);
        }

        $this->relationships->fixForeignRecordKeys($record);
        $this->relationships->persistAfterNative($record, $tracker);

        return true;
    }

    /**
     *
     * Returns a new Record object.
     *
     * @param array $fields Populate the Record fields with these values.
     *
     * @return RecordInterface If a Mapper-specific Record class is defined,
     * that will be returned instead of a generic Record.
     *
     */
    public function newRecord(array $fields = []) : RecordInterface
    {
        $row = $this->table->newRow($fields);
        $record = $this->newRecordFromRow($row);
        $record->getRelated()->set($fields);
        return $record;
    }

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
    public function newRecordSet(array $records = []) : RecordSetInterface
    {
        $recordSetClass = $this->getRecordSetClass();
        return new $recordSetClass(
            $records,
            [$this, 'newRecord']
        );
    }

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
    public function turnRowIntoRecord(RowInterface $row, array $with = []) : RecordInterface
    {
        $record = $this->newRecordFromRow($row);
        $this->relationships->stitchIntoRecords([$record], $with);
        return $record;
    }

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
    public function turnRowsIntoRecords(array $rows, array $with = []) : array
    {
        $records = [];
        foreach ($rows as $row) {
            $records[] = $this->newRecordFromRow($row);
        }
        $this->relationships->stitchIntoRecords($records, $with);
        return $records;
    }

    /**
     *
     * Use this in extended Mapper classes to set the relationships.
     *
     * @return void
     *
     */
    protected function setRelated() // typehinting will break extended classes
    {
    }

    /**
     *
     * Sets a one-to-one relationship to another mapper.
     *
     * @param string $name The field name to use in the Record for the related
     * foreign Record.
     *
     * @param string $foreignMapperClass The class name of the foreign mapper.
     *
     * @return RelationshipInterface
     *
     */
    protected function oneToOne(string $name, string $foreignMapperClass) : RelationshipInterface
    {
        $this->assertRelatedName($name);
        return $this->relationships->oneToOne(
            $name,
            get_class($this),
            $foreignMapperClass
        );
    }

    /**
     *
     * Sets a one-to-many relationship to another mapper.
     *
     * @param string $name The field name to use in the Record for the related
     * foreign RecordSet.
     *
     * @param string $foreignMapperClass The class name of the foreign mapper.
     *
     * @return RelationshipInterface
     *
     */
    protected function oneToMany(string $name, string $foreignMapperClass) : RelationshipInterface
    {
        $this->assertRelatedName($name);
        return $this->relationships->oneToMany(
            $name,
            get_class($this),
            $foreignMapperClass
        );
    }

    /**
     *
     * Sets a many-to-one relationship to another mapper.
     *
     * @param string $name The field name to use in the Record for the related
     * foreign Record.
     *
     * @param string $foreignMapperClass The class name of the foreign mapper.
     *
     * @return RelationshipInterface
     *
     */
    protected function manyToOne(string $name, string $foreignMapperClass) : RelationshipInterface
    {
        $this->assertRelatedName($name);
        return $this->relationships->manyToOne(
            $name,
            get_class($this),
            $foreignMapperClass
        );
    }

    /**
     *
     * Sets a many-to-one relationship to another mapper by reference.
     *
     * @param string $name The field name to use in the Record for the related
     * foreign Record.
     *
     * @param string $referenceCol The native table reference column name.
     *
     * @return RelationshipInterface
     *
     */
    protected function manyToOneByReference(string $name, string $referenceCol) : RelationshipInterface
    {
        $this->assertRelatedName($name);
        return $this->relationships->manyToOneByReference(
            $name,
            get_class($this),
            $referenceCol
        );
    }

    /**
     *
     * Sets a many-to-many relationship to another mapper.
     *
     * @param string $name The field name to use in the Record for the related
     * foreign RecordSet.
     *
     * @param string $foreignMapperClass The class name of the foreign mapper.
     *
     * @param string $throughName Relate to the foreign mapper through this
     * native Record field name.
     *
     * @return RelationshipInterface
     *
     */
    protected function manyToMany(string $name, string $foreignMapperClass, string $throughName) : RelationshipInterface
    {
        $this->assertRelatedName($name);
        return $this->relationships->manyToMany(
            $name,
            get_class($this),
            $foreignMapperClass,
            $throughName
        );
    }

    /**
     *
     * Assert that a "related" name does not conflict with an existing column
     * name.
     *
     * @param string $name The related name.
     *
     * @throws Exception if the name conflicts with a column name.
     *
     */
    protected function assertRelatedName(string $name) : void
    {
        if (in_array($name, $this->getTable()->getColNames())) {
            throw Exception::relatedNameConflict($name);
        }
    }

    /**
     *
     * Returns the name of the Record class to use for a particular Row.
     *
     * By default, this method returns the Record class specific to this Mapper
     * if one exists; otherwise it returns the generic Record class name.
     *
     * Override this method to implement single-table inheritance so that
     * different Record class names can be returned based on a Row column value.
     *
     * @param RowInterface $row Return a Record class name for this Row.
     *
     * @return string
     *
     */
    protected function getRecordClass(RowInterface $row) : string
    {
        static $recordClass;
        if (! $recordClass) {
            $recordClass = substr(get_class($this), 0, -6) . 'Record';
            $recordClass = class_exists($recordClass)
                ? $recordClass
                : Record::CLASS;
        }
        return $recordClass;
    }

    /**
     *
     * Returns the name of the RecordSet class to use with this Mapper.
     *
     * By default, this method returns the RecordSet class specific to this
     * Mapper if one exists; otherwise it returns the generic RecordSet class
     * name.
     *
     * @return string
     *
     */
    protected function getRecordSetClass() : string
    {
        static $recordSetClass;
        if (! $recordSetClass) {
            $recordSetClass = substr(get_class($this), 0, -6) . 'RecordSet';
            $recordSetClass = class_exists($recordSetClass)
                ? $recordSetClass
                : RecordSet::CLASS;
        }
        return $recordSetClass;
    }

    /**
     *
     * Given a Row object, returns a new Record.
     *
     * @param RowInterface $row The Row for the Record.
     *
     * @return RecordInterface
     *
     */
    protected function newRecordFromRow(RowInterface $row) : RecordInterface
    {
        $recordClass = $this->getRecordClass($row);
        return new $recordClass(
            get_class($this),
            $row,
            $this->newRelated()
        );
    }

    /**
     *
     * Returns a new Related object for related fields on a Record.
     *
     * @return Related
     *
     */
    protected function newRelated() : Related
    {
        return new Related($this->relationships->getFields());
    }
}
