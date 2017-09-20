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

/**
 *
 * Represents a single row in a table.
 *
 * @package atlas/orm
 *
 */
class Row implements RowInterface
{
    /**
     * The row has been deleted from the table; modification is not allowed.
     */
    const DELETED = 'DELETED';

    /**
     * The row was selected/inserted/updated, and has been modified.
     */
    const MODIFIED = 'MODIFIED';

    /**
     * The row is in memory only and not in the table.
     */
    const FOR_INSERT = 'FOR_INSERT';

    /**
     * The row is marked for later deletion; modification is allowed.
     */
    const FOR_DELETE = 'FOR_DELETE';

    /**
     * The row was inserted into the table, and is unmodified.
     */
    const INSERTED = 'INSERTED';

    /**
     * The row was selected from the table, and is unmodified.
     */
    const SELECTED = 'SELECTED';

    /**
     * The row was updated in the table, and is unmodified.
     */
    const UPDATED = 'UPDATED';

    /**
     *
     * Row columns and their corresponding values.
     *
     * @var array
     *
     */
    private $cols = [];

    /**
     *
     * The status of this Row.
     *
     * @var string
     *
     */
    private $status;

    /**
     *
     * Constructor.
     *
     * @param array $cols The row columns and their corresponding values.
     *
     */
    public function __construct(array $cols)
    {
        foreach ($cols as $col => $val) {
            $this->assertValid($val);
            $this->cols[$col] = $val;
        }
        $this->status = static::FOR_INSERT;
    }

    /**
     *
     * Allows read access to column values as properties.
     *
     * @param string $col The column name.
     *
     * @return mixed The column value.
     *
     * @throws Exception when the column does not exist.
     *
     */
    public function __get(string $col)
    {
        $this->assertHas($col);
        return $this->cols[$col];
    }

    /**
     *
     * Allows write access to column values as properties.
     *
     * @param string $col The column name.
     *
     * @param mixed $val The column value.
     *
     * @throws Exception when the column does not exist.
     *
     */
    public function __set(string $col, $val) : void
    {
        $this->assertHas($col);
        $this->modify($col, $val);
    }

    /**
     *
     * Allows isset() access to column values as properties.
     *
     * @param string $col The column name.
     *
     * @throws Exception when the column does not exist.
     *
     * @return bool
     *
     */
    public function __isset(string $col) : bool
    {
        $this->assertHas($col);
        return isset($this->cols[$col]);
    }

    /**
     *
     * Allows unset() access to column values as properties.
     *
     * @param string $col The column name.
     *
     * @throws Exception when the column does not exist.
     *
     */
    public function __unset(string $col) : void
    {
        $this->assertHas($col);
        $this->modify($col, null);
    }

    /**
     *
     * Sets multiple column values at once.
     *
     * @param array $cols The columns and their corresponding values.
     *
     * @throws Exception when a column does not exist.
     *
     */
    public function set(array $cols = []) : void
    {
        foreach ($cols as $col => $val) {
            if ($this->has($col)) {
                $this->modify($col, $val);
            }
        }
    }

    /**
     *
     * Does the row have a particular column?
     *
     * @param string $col Check for the existence of this column.
     *
     * @return bool
     *
     */
    public function has(string $col) : bool
    {
        return array_key_exists($col, $this->cols);
    }

    /**
     *
     * Returns an array copy of this row.
     *
     * @return array
     *
     */
    public function getArrayCopy() : array
    {
        return $this->cols;
    }

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
    public function getArrayDiff(array $init) : array
    {
        $diff = $this->getArrayCopy();
        foreach ($diff as $col => $val) {
            $same = array_key_exists($col, $init)
                && $this->isEquivalent($init[$col], $diff[$col]);
            if ($same) {
                unset($diff[$col]);
            }
        }
        return $diff;
    }

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
    public function hasStatus($status) : bool
    {
        return in_array($this->status, (array) $status);
    }

    /**
     *
     * Returns the row status.
     *
     * @return string
     *
     */
    public function getStatus() : string
    {
        return $this->status;
    }

    /**
     *
     * Forces the row to a particular status.
     *
     * @param string $status The new status for the row.
     *
     * @throws Exception when the status is invalid.
     *
     */
    public function setStatus(string $status) : void
    {
        $const = get_class($this) . '::' . $status;
        if (! defined($const)) {
            throw Exception::invalidStatus($status);
        }
        $this->status = $status;
    }

    /**
     *
     * Implements JsonSerializable::jsonSerialize().
     *
     */
    public function jsonSerialize() : array
    {
        return $this->getArrayCopy();
    }

    /**
     *
     * Protects the row against disallowed modifications, and sets the row
     * status to MODIFIED as appropriate.
     *
     * @param string $col The column name.
     *
     * @param mixed $new The new column value.
     *
     * @throws Exception when modification is not allowed.
     *
     */
    protected function modify(string $col, $new) : void
    {
        if ($this->status == static::DELETED) {
            throw Exception::immutableOnceDeleted($this, $col);
        }

        $this->assertValid($new);

        if ($this->status == static::FOR_INSERT) {
            $this->cols[$col] = $new;
            return;
        }

        $old = $this->cols[$col];
        $this->cols[$col] = $new;
        if (! $this->isEquivalent($old, $new)) {
            $this->setStatus(static::MODIFIED);
        }
    }

    /**
     *
     * Asserts that a value is null or scalar.
     *
     * @param mixed $value The value to check.
     *
     * @throws Exception when non-null and non-scalar.
     *
     */
    protected function assertValid($value) : void
    {
        if (! is_null($value) && ! is_scalar($value)) {
            throw Exception::invalidType('scalar or null', $value);
        }
    }

    /**
     *
     * Asserts that a column exists.
     *
     * @param string $col The column name.
     *
     * @throws Exception when the column does not exist.
     *
     */
    protected function assertHas($col) : void
    {
        if (! $this->has($col)) {
            throw Exception::propertyDoesNotExist($this, $col);
        }
    }

    /**
     *
     * Are two values equivalent to each other? Compares numeric values loosely,
     * and non-numeric values strictly.
     *
     * @param mixed $old The old value.
     *
     * @param mixed $new The new value.
     *
     * @return bool
     *
     */
    protected function isEquivalent($old, $new) : bool
    {
        return (is_numeric($old) && is_numeric($new))
            ? $old == $new // numeric, compare loosely
            : $old === $new; // not numeric, compare strictly
    }
}
