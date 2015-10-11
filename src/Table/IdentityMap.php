<?php
namespace Atlas\Table;

use Atlas\Exception;
use SplObjectStorage;

class IdentityMap
{
    /**
     * @var array
     */
    protected $serialToRow = [];

    /**
     * @var SplObjectStorage
     */
    protected $rowToSerial;

    /**
     * @var SplObjectStorage
     */
    protected $initial;

    public function __construct()
    {
        $this->rowToSerial = new SplObjectStorage();
        $this->initial = new SplObjectStorage();
    }

    /**
     * @param Row $row
     */
    public function setRow(Row $row)
    {
        if ($this->hasRow($row)) {
            throw new Exception('Row already exists in IdentityMap');
        }

        $serial = $this->getSerial($row->getIdentity()->getPrimary());
        $this->serialToRow[$serial] = $row;
        $this->rowToSerial[$row] = $serial;
        $this->setInitial($row);
    }

    /**
     * @param Row $row
     * @return boolean
     */
    public function hasRow($row)
    {
        return isset($this->rowToSerial[$row]);
    }

    /**
     * @param mixed $primary
     * @return boolean
     */
    public function hasPrimary($primary)
    {
        $serial = $this->getSerial($primary);
        return isset($this->serialToRow[$serial]);
    }

    /**
     * @param mixed $primary
     * @return Row
     */
    public function getRowByPrimary($primary)
    {
        $serial = $this->getSerial($primary);
        if (! isset($this->serialToRow[$serial])) {
            return false;
        }

        return $this->serialToRow[$serial];
    }

    /**
     *
     * This is a ghetto hack to serialize a composite primary key to a string,
     * so it can be used for array key lookups. It works just as well for
     * single-value keys as well.
     *
     * All it does it implode() the primary values with a pipe (to make it
     * easier for people to see the separator) and an ASCII "unit separator"
     * character (to include something that is unlikely to be used in a real
     * primary-key value, and thus help prevent the serial string from being
     * subverted).
     *
     * WARNING: You should sanitize your primary-key values to disallow ASCII
     * character 31 (hex 1F) to keep the lookup working properly. This is only
     * a problem with non-integer keys
     *
     * WARNING: Null, false, and empty-string key values are treated as
     * identical by this algorithm. That means these values are interchangeable
     * and are not differentiated. You should sanitize your primary-key values
     * to disallow null, false, and empty-string values. This is only a problem
     * with non-integer keys.
     *
     * WARNING: The serial string version of the primary key depends on the
     * values always being in the same order. E.g., `['foo' => 1, 'bar' => 2]`
     * will result in a different serial than `['bar' => 2, 'foo' => 1]`, even
     * though the key-value pairs themselves are the same.
     *
     */
    public function getSerial($primary)
    {
        $separator = "|\x1F"; // a pipe, and ASCII 31 ("unit separator")
        $serial = $separator
                . implode($separator, (array) $primary)
                . $separator;
        return $serial;
    }

    public function setInitial(Row $row)
    {
        if (! $this->hasRow($row)) {
            throw new Exception('Row does not exist in IdentityMap');
        }

        $this->initial[$row] = $row->getArrayCopy();
    }

    public function getInitial(Row $row)
    {
        if (! $this->hasRow($row)) {
            throw new Exception('Row does not exist in IdentityMap');
        }

        return $this->initial[$row];
    }
}
