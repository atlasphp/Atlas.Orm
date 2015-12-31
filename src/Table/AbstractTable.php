<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\Mapper\Select;

abstract class AbstractTable implements TableInterface
{
    public function __construct(IdentityMap $identityMap)
    {
        $this->identityMap = $identityMap;
    }

    /**
     *
     * Returns the table name.
     *
     * @return string
     *
     */
    abstract public function getName();

    /**
     *
     * Returns the table column names.
     *
     * @return array
     *
     */
    abstract public function getColNames();

    /**
     *
     * Returns the table column information.
     *
     * @return array
     *
     */
    abstract public function getCols();

    /**
     *
     * Returns the primary key on the table, typically a single column name.
     *
     * @return string The primary column name.
     *
     */
    abstract public function getPrimaryKey();

    /**
     *
     * Does the database set the primary key value on insert by autoincrement?
     *
     * @return bool
     *
     */
    abstract public function getAutoinc();

    /**
     *
     * Returns the default values for a new row.
     *
     * @return array
     *
     */
    abstract public function getColDefaults();

    /**
     *
     * Returns a new Row for this table.
     *
     * @return RowInterface
     *
     */
    public function newRow(array $cols = [])
    {
        $cols = array_merge($this->getColDefaults(), $cols);
        $primary = $this->newPrimary($cols);
        $rowClass = $this->getRowClass();
        $row = new $rowClass(get_class($this), $primary, $cols);
        return $row;
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
        $primaryVal = $cols[$this->getPrimaryKey()];
        $primaryIdentity = $this->getPrimaryIdentity($primaryVal);
        $row = $this->identityMap->getRowByPrimary(
            get_class($this),
            $primaryIdentity
        );
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
            $hasPrimary = $this->identityMap->hasPrimary(
                get_class($this),
                $primaryIdentity
            );
            if ($hasPrimary) {
                $rows[$primaryVal] = $this->identityMap->getRowByPrimary(
                    get_class($this),
                    $primaryIdentity
                );
                unset($primaryVals[$i]);
            }
        }

        // are there still rows to fetch?
        if (! $primaryVals) {
            return array_values($rows);
        }

        // fetch and retain remaining rows
        $colsVals = [$this->getPrimaryKey() => $primaryVals];
        $select->colsVals($this->getName(), $colsVals);
        $data = $select->cols($this->getColNames())->fetchAll();
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
        return [$this->getPrimaryKey() => $primaryVal];
    }

    protected function newPrimary(array &$cols)
    {
        $primaryCol = $this->getPrimaryKey();
        $primaryVal = null;
        if (array_key_exists($primaryCol, $cols)) {
            $primaryVal = $cols[$primaryCol];
            unset($cols[$primaryCol]);
        }
        return new Primary([$primaryCol => $primaryVal]);
    }

    protected function getRowClass()
    {
        static $rowClass;
        if (! $rowClass) {
            $rowClass = substr(get_class($this), 0, -5) . 'Row';
            $rowClass = class_exists($rowClass)
                ? $rowClass
                : Row::CLASS;
        }
        return $rowClass;
    }
}
