<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Exception;
use Atlas\Orm\Relationship\ManyToMany;
use Atlas\Orm\Relationship\ManyToOne;
use Atlas\Orm\Relationship\OneToMany;
use Atlas\Orm\Relationship\OneToOne;
use Atlas\Orm\Relationship\Relationships;
use Atlas\Orm\Table\IdentityMap;
use Atlas\Orm\Table\RowInterface;
use Atlas\Orm\Table\TableInterface;
use Aura\Sql\ConnectionLocator;
use Aura\SqlQuery\QueryFactory;

/**
 *
 * A data source mapper that returns Record and RecordSet objects.
 *
 * @package Atlas.Atlas
 *
 */
abstract class AbstractMapper implements MapperInterface
{
    protected $table;

    protected $relationships;

    protected $events;

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

    static public function getTableClass()
    {
        static $tableClass;
        if (! $tableClass) {
            $tableClass = substr(get_called_class(), 0, -6) . 'Table';
        }
        return $tableClass;
    }

    public function getTable()
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
    public function getReadConnection()
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
    public function getWriteConnection()
    {
        return $this->table->getWriteConnection();
    }

    public function fetchRecord($primaryVal, array $with = [])
    {
        $row = $this->table->fetchRow($primaryVal);
        if (! $row) {
            return false;
        }
        return $this->newRecordFromRow($row, $with);
    }

    public function fetchRecordSet(array $primaryVals, array $with = [])
    {
        $rows = $this->table->fetchRows($primaryVals);
        if (! $rows) {
            return [];
        }
        return $this->newRecordSetFromRows($rows, $with);
    }

    public function fetchRecordBy(array $colsVals = [], array $with = [])
    {
        $row = $this->table->selectRow($this->table->select($colsVals));
        if (! $row) {
            return false;
        }
        return $this->newRecordFromRow($row, $with);
    }

    public function fetchRecordSetBy(array $colsVals = [], array $with = [])
    {
        $rows = $this->table->selectRows($this->table->select($colsVals));
        if (! $rows) {
            return [];
        }
        return $this->newRecordSetFromRows($rows, $with);
    }

    public function select(array $colsVals = [])
    {
        return new MapperSelect(
            $this->table->select($colsVals),
            [$this, 'getSelectedRecord'],
            [$this, 'getSelectedRecordSet']
        );
    }

    /**
     *
     * Inserts the Row for a Record.
     *
     * @param RecordInterface $record Insert the Row for this Record.
     *
     * @return mixed
     *
     */
    public function insert(RecordInterface $record)
    {
        $this->events->beforeInsert($this, $record);
        $result = $this->table->insert($record->getRow());
        $this->events->afterInsert($this, $record, $result);
        return $result;
    }

    /**
     *
     * Updates the Row for a Record.
     *
     * @param RecordInterface $record Update the Row for this Record.
     *
     * @return mixed
     *
     */
    public function update(RecordInterface $record)
    {
        $this->events->beforeUpdate($this, $record);
        $result = $this->table->update($record->getRow());
        $this->events->afterUpdate($this, $record, $result);
        return $result;
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
    public function delete(RecordInterface $record)
    {
        $this->events->beforeDelete($this, $record);
        $result = $this->table->delete($record->getRow());
        $this->events->afterDelete($this, $record, $result);
        return $result;
    }

    public function newRecord(array $cols = [])
    {
        $row = $this->table->newRow($cols);
        return $this->newRecordFromRow($row);
    }

    public function newRecordSet(array $records = [], array $with = [])
    {
        $recordSetClass = $this->getRecordSetClass();
        $recordSet = new $recordSetClass($records);
        $this->relationships->stitchIntoRecordSet($recordSet, $with);
        return $recordSet;
    }

    public function getSelectedRecord(array $cols, array $with = [])
    {
        $row = $this->table->getSelectedRow($cols);
        return $this->newRecordFromRow($row, $with);
    }

    public function getSelectedRecordSet(array $data, array $with = [])
    {
        $records = [];
        foreach ($data as $cols) {
            $records[] = $this->getSelectedRecord($cols);
        }
        return $this->newRecordSet($records, $with);
    }

    protected function setRelated()
    {
    }

    protected function oneToOne($name, $foreignMapperClass)
    {
        return $this->relationships->set(
            get_class($this),
            $name,
            OneToOne::CLASS,
            $foreignMapperClass
        );
    }

    protected function oneToMany($name, $foreignMapperClass)
    {
        return $this->relationships->set(
            get_class($this),
            $name,
            OneToMany::CLASS,
            $foreignMapperClass
        );
    }

    protected function manyToOne($name, $foreignMapperClass)
    {
        return $this->relationships->set(
            get_class($this),
            $name,
            ManyToOne::CLASS,
            $foreignMapperClass
        );
    }

    protected function manyToMany($name, $foreignMapperClass, $throughName)
    {
        return $this->relationships->set(
            get_class($this),
            $name,
            ManyToMany::CLASS,
            $foreignMapperClass,
            $throughName
        );
    }

    protected function getRecordClass(RowInterface $row)
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

    protected function getRecordSetClass()
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

    protected function newRecordFromRow(RowInterface $row, array $with = [])
    {
        $recordClass = $this->getRecordClass($row);
        $record = new $recordClass(
            get_class($this),
            $row,
            $this->newRelated()
        );
        $this->relationships->stitchIntoRecord($record, $with);
        return $record;
    }

    protected function newRecordSetFromRows(array $rows, array $with = [])
    {
        $records = [];
        foreach ($rows as $row) {
            $records[] = $this->newRecordFromRow($row);
        }
        return $this->newRecordSet($records, $with);
    }

    protected function newRelated()
    {
        return new Related($this->relationships->getFields());
    }
}
