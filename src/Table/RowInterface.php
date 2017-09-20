<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;
use JsonSerializable;

/**
 *
 * Interface for Row objects.
 *
 * @package atlas/orm
 *
 */
interface RowInterface extends JsonSerializable
{
    /**
     *
     * Sets multiple column values at once.
     *
     * @param array $cols The columns and their corresponding values.
     *
     * @throws Exception when a column does not exist.
     *
     */
    public function set(array $cols) : void;

    /**
     *
     * Does the row have a particular column?
     *
     * @param string $col Check for the existence of this column.
     *
     * @return bool
     *
     */
    public function has(string $col) : bool;

    /**
     *
     * Returns an array copy of this row.
     *
     * @return array
     *
     */
    public function getArrayCopy() : array;

    /**
     *
     * Given an array of "initial" values, returns an array of the different
     * values on this row.
     *
     * @param array $init Initial values to compare to.
     *
     * @return array The different values on this row.
     *
     */
    public function getArrayDiff(array $init) : array;

    /**
     *
     * Does the row have a particular status?
     *
     * @param string|array $status One or more status values.
     *
     * @return bool True if the row matches any of the $status values, false
     * if not.
     *
     */
    public function hasStatus($status) : bool;

    /**
     *
     * Returns the row status.
     *
     * @return string
     *
     */
    public function getStatus() : string;

    /**
     *
     * Forces the row to a particular status.
     *
     * @param string $status The new status for the row.
     *
     * @throws Exception when the status is invalid.
     *
     */
    public function setStatus(string $status) : void;
}
