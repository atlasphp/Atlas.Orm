<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\Mapper\Select;
use Aura\SqlQuery\QueryFactory;
use Aura\SqlQuery\Common\SelectInterface;

/**
 * Not so much a gateway as a secretary.
 */
class Gateway
{
    protected $table;
    protected $queryFactory;
    protected $identityMap;

    public function __construct(
        TableInterface $table,
        QueryFactory $queryFactory,
        IdentityMap $identityMap
    ) {
        $this->table = $table;
        $this->queryFactory = $queryFactory;
        $this->identityMap = $identityMap;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getIdentityMap()
    {
        return $this->identityMap;
    }

    public function selectRow(Select $select)
    {
        $cols = $select->cols($this->table->getColNames())->fetchOne();
        if (! $cols) {
            return false;
        }
        return $this->getIdentifiedOrSelectedRow($cols);
    }

    public function selectRows(Select $select)
    {
        $data = $select->cols($this->table->getColNames())->fetchAll();
        if (! $data) {
            return [];
        }

        $rows = [];
        foreach ($data as $cols) {
            $rows[] = $this->getIdentifiedOrSelectedRow($cols);
        }

        return $rows;
    }

    public function newSelect(array $colsVals = [])
    {
        $select = $this->queryFactory->newSelect();
        $table = $this->table->getName();
        $select->from($table);
        foreach ($colsVals as $col => $val) {
            $this->selectWhere($select, "{$table}.{$col}", $val);
        }
        return $select;
    }

    protected function selectWhere($select, $col, $val)
    {
        if (is_array($val)) {
            $select->where("{$col} IN (?)", $val);
            return;
        }

        if ($val === null) {
            $select->where("{$col} IS NULL");
            return;
        }

        $select->where("{$col} = ?", $val);
    }

    public function newInsert(RowInterface $row)
    {
        $insert = $this->queryFactory->newInsert();
        $insert->into($this->table->getName());

        $cols = $row->getArrayCopy();
        if ($this->table->getAutoinc()) {
            unset($cols[$this->table->getPrimaryKey()]);
        }
        $insert->cols($cols);

        return $insert;
    }

    public function newUpdate(RowInterface $row)
    {
        $update = $this->queryFactory->newUpdate();
        $update->table($this->table->getName());

        $cols = $row->getArrayDiff($this->identityMap->getInitial($row));
        unset($cols[$this->table->getPrimaryKey()]);
        $update->cols($cols);

        $primaryCol = $this->table->getPrimaryKey();
        $update->where("{$primaryCol} = ?", $row->getPrimary()->getVal());

        return $update;
    }

    public function newDelete(RowInterface $row)
    {
        $delete = $this->queryFactory->newDelete();
        $delete->from($this->table->getName());

        $primaryCol = $this->table->getPrimaryKey();
        $delete->where("{$primaryCol} = ?", $row->getPrimary()->getVal());

        return $delete;
    }

    public function inserted(RowInterface $row)
    {
        $row->setStatus($row::IS_INSERTED);
        $this->identityMap->setRow($row, $row->getArrayCopy());
    }

    public function updated(RowInterface $row)
    {
        $row->setStatus($row::IS_UPDATED);
        $this->identityMap->setInitial($row);
    }

    public function deleted(RowInterface $row)
    {
        $row->setStatus($row::IS_DELETED);
    }

    /**
     *
     * Returns a new Row for the table.
     *
     * @return RowInterface
     *
     */
    public function newRow(array $cols = [])
    {
        $cols = array_merge($this->table->getColDefaults(), $cols);
        $primary = $this->newPrimary($cols);
        $rowClass = $this->table->getRowClass();
        $row = new $rowClass($primary, $cols);
        return $row;
    }

    public function getIdentifiedRow($primaryVal)
    {
        $primaryIdentity = $this->getPrimaryIdentity($primaryVal);
        return $this->identityMap->getRowByPrimary($primaryIdentity);
    }

    public function newSelectedRow(array $cols)
    {
        $row = $this->newRow($cols);
        $row->setStatus($row::IS_CLEAN);
        $this->identityMap->setRow($row, $cols);
        return $row;
    }

    public function getIdentifiedOrSelectedRow(array $cols)
    {
        $primaryVal = $cols[$this->table->getPrimaryKey()];
        $primaryIdentity = $this->getPrimaryIdentity($primaryVal);
        $row = $this->identityMap->getRowByPrimary($primaryIdentity);
        if (! $row) {
            $row = $this->newSelectedRow($cols);
        }
        return $row;
    }

    /*
    Retrieve rows from identity map and/or database.

    Rows by primary:
        create empty rows
        foreach primary value ...
            add null in rows keyed on primary value to maintain place
            if primary value in map
                retain mapped row in set keyed on primary value
                remove primary value from list
        select remaining primary values
        foreach returned one ...
            new row object
            retain row in map
            add row in set on ID key
        return rows
    */
    public function identifyOrSelectRows(array $primaryVals, Select $select)
    {
        if (! $primaryVals) {
            return [];
        }

        $rows = [];
        foreach ($primaryVals as $i => $primaryVal) {
            $rows[$primaryVal] = null;
            $primaryIdentity = $this->getPrimaryIdentity($primaryVal);
            $hasPrimary = $this->identityMap->hasPrimary($primaryIdentity);
            if ($hasPrimary) {
                $rows[$primaryVal] = $this->identityMap->getRowByPrimary($primaryIdentity);
                unset($primaryVals[$i]);
            }
        }

        // are there still rows to fetch?
        if (! $primaryVals) {
            return array_values($rows);
        }

        // fetch and retain remaining rows
        $this->selectWhere($select, $this->table->getPrimaryKey(), $primaryVals);
        $data = $select->cols($this->table->getColNames())->fetchAll();
        foreach ($data as $cols) {
            $row = $this->newSelectedRow($cols);
            $rows[$row->getPrimary()->getVal()] = $row;
        }

        // remove unfound rows
        foreach ($rows as $key => $val) {
            if ($val === null) {
                unset($rows[$key]);
            }
        }

        // done
        return array_values($rows);
    }

    public function getPrimaryIdentity($primaryVal)
    {
        return [$this->table->getPrimaryKey() => $primaryVal];
    }

    protected function newPrimary(array &$cols)
    {
        $primaryCol = $this->table->getPrimaryKey();
        $primaryVal = null;
        if (array_key_exists($primaryCol, $cols)) {
            $primaryVal = $cols[$primaryCol];
            unset($cols[$primaryCol]);
        }
        return new Primary([$primaryCol => $primaryVal]);
    }

    public function select()
    {

    }

    public function insert(RowInterface $row)
    {

    }
}