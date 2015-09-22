<?php
namespace Atlas\Table;

use Atlas\Exception;
use SplObjectStorage;

class IdentityMap
{
    /**
     * @var array
     */
    protected $primaryValToRow;

    /**
     * @var SplObjectStorage
     */
    protected $rowToPrimaryVal;

    public function __construct()
    {
        $this->rowToPrimaryVal = new SplObjectStorage();
        $this->primaryValToRow = [];
    }

    /**
     * @param Row $row
     */
    public function set(Row $row)
    {
        if ($this->hasRow($row)) {
            throw new Exception('Row already exists in IdentityMap');
        }

        $primaryVal = $row->getPrimaryVal();
        $this->primaryValToRow[$primaryVal] = $row;
        $this->rowToPrimaryVal[$row] = $primaryVal;
    }

    /**
     * @param Row $row
     * @return boolean
     */
    public function hasRow($row)
    {
        return isset($this->rowToPrimaryVal[$row]);
    }

    /**
     * @param mixed $primaryVal
     * @return boolean
     */
    public function hasPrimaryVal($primaryVal)
    {
        return isset($this->primaryValToRow[$primaryVal]);
    }

    /**
     * @param mixed $primaryVal
     * @return Row
     */
    public function getRow($primaryVal)
    {
        if (! $this->hasPrimaryVal($primaryVal)) {
            return false;
        }

        return $this->primaryValToRow[$primaryVal];
    }

    /**
     * @param Row $row
     * @return mixed
     */
    public function getPrimaryVal($row)
    {
        if (! $this->hasRow($row)) {
            return false;
        }

        return $this->rowToPrimaryVal[$row];
    }
}