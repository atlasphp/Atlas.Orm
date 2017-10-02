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
use Atlas\Orm\Table\RowInterface;

/**
 *
 * A generic Record, composed of a Row and Related records/recordsets.
 *
 * @package atlas/orm
 *
 */
class Record implements RecordInterface
{
    /**
     *
     * The Mapper class for this Record.
     *
     * @param string
     *
     */
    private $mapperClass;

    /**
     *
     * The native Row for the Record.
     *
     * @var RowInterface
     *
     */
    private $row;

    /**
     *
     * The related foreign Record and RecordSet objects.
     *
     * @var Related
     *
     */
    private $related;

    /**
     *
     * Is this Record marked for deletion?
     *
     * @var bool
     *
     */
    private $delete = false;

    /**
     *
     * Constructor.
     *
     * @param string $mapperClass The Mapper class for this Record.
     *
     * @param RowInterface $row The native Row for this Record.
     *
     * @param Related $related The related foreign Record and RecordSet objects.
     *
     */
    public function __construct(string $mapperClass, RowInterface $row, Related $related)
    {
        $this->mapperClass = $mapperClass;
        $this->row = $row;
        $this->related = $related;
    }

    /**
     *
     * Allows read access to Row and Related fields as properties.
     *
     * @param string $field The Row or Related field name.
     *
     * @return mixed
     *
     */
    public function __get(string $field)
    {
        $prop = $this->assertHas($field);
        return $this->$prop->$field;
    }

    /**
     *
     * Allows write access to Row and Related fields as properties.
     *
     * @param string $field The Row or Related field name.
     *
     * @param mixed $value Set the field to this value.
     *
     * @return mixed
     *
     */
    public function __set(string $field, $value) : void
    {
        $prop = $this->assertHas($field);
        $this->$prop->$field = $value;
    }

    /**
     *
     * Allows isset() access to Row and Related fields as properties.
     *
     * @param string $field The Row or Related field name.
     *
     * @return bool
     *
     */
    public function __isset(string $field) : bool
    {
        $prop = $this->assertHas($field);
        return isset($this->$prop->$field);
    }

    /**
     *
     * Allows unset() access to Row and Related fields as properties.
     *
     * @param string $field The Row or Related field name.
     *
     * @return void
     *
     */
    public function __unset($field) : void
    {
        $prop = $this->assertHas($field);
        unset($this->$prop->$field);
    }

    /**
     *
     * Returns the Mapper class for this Record.
     *
     * @return string
     *
     */
    public function getMapperClass() : string
    {
        return $this->mapperClass;
    }

    /**
     *
     * Gets the native Row for this Record.
     *
     * @return RowInterface
     *
     */
    public function getRow() : RowInterface
    {
        return $this->row;
    }

    /**
     *
     * Gets the related foreign Record and RecordSet objects for this Record.
     *
     * @return Related
     *
     */
    public function getRelated() : Related
    {
        return $this->related;
    }

    /**
     *
     * Sets many field values at one time.
     *
     * @param array $fieldsValues An array of key-value pairs where the key is
     * the field name and the value is the value to set.
     *
     */
    public function set(array $fieldsValues) : void
    {
        foreach ($fieldsValues as $field => $value) {
            if ($this->row->has($field)) {
                $this->row->$field = $value;
            } elseif ($this->related->has($field)) {
                $this->related->$field = $value;
            }
        }
    }

    /**
     *
     * Does the Record have a particular field?
     *
     * @param string $field The Row or Related field name.
     *
     * @return bool
     *
     */
    public function has($field) : bool
    {
        return $this->row->has($field)
            || $this->related->has($field);
    }

    /**
     *
     * Returns an array of the Row and Related fields for this Record.
     *
     * @return array
     *
     */
    public function getArrayCopy() : array
    {
        // use +, not array_merge(), so row takes precedence over related
        return $this->row->getArrayCopy()
             + $this->related->getArrayCopy();
    }

    /**
     *
     * Implements JsonSerializable::jsonSerialize().
     *
     * @return array
     *
     */
    public function jsonSerialize() : array
    {
        return $this->getArrayCopy();
    }

    /**
     *
     * Asserts that a field exists on the Row or Related.
     *
     * @param string $field The Row or Related field name.
     *
     * @return string If the Row has the field, returns 'row'; if the Related
     * has the field, returns 'related'.
     *
     * @throws Exception When neither the Row nor the Related has the field.
     *
     */
    protected function assertHas($field) : string
    {
        if ($this->row->has($field)) {
            return 'row';
        }

        if ($this->related->has($field)) {
            return 'related';
        }

        throw Exception::propertyDoesNotExist($this, $field);
    }

    /**
     *
     * Marks this Record for deletion.
     *
     * @param bool $delete True to mark for deletion, false to unmark.
     *
     */
    public function markForDeletion($delete = true) : void
    {
        $this->delete = (bool) $delete;
    }

    /**
     *
     * Returns the persistence method that will be used for this Record (one of
     * insert/update/delete) or null if no method will be used.
     *
     * @return ?string
     *
     */
    public function getPersistMethod() : ?string
    {
        if ($this->delete) {
            return 'delete';
        }

        switch ($this->row->getStatus()) {
            case $this->row::FOR_INSERT:
                return 'insert';
            case $this->row::MODIFIED:
                return 'update';
            case $this->row::FOR_DELETE:
                return 'delete';
        }

        return null;
    }
}
