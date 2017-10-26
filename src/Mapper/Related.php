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

/**
 *
 * A collection of foreign related Record and RecordSet objects.
 *
 * @package atlas/orm
 *
 */
class Related
{
    /**
     *
     * The field names with their Record and RecordSet objects.
     *
     * @param array
     *
     */
    private $fields = [];

    /**
     *
     * Constructor.
     *
     * @param array $fields The field names with their Record and RecordSet
     * objects.
     *
     */
    public function __construct(array $fields = [])
    {
        foreach ($fields as $name => $value) {
            $this->modify($name, $value);
        }
    }

    /**
     *
     * Allows read access to related fields as properties.
     *
     * @param string $name The related field name.
     *
     * @return mixed
     *
     */
    public function __get(string $name)
    {
        $this->assertHas($name);
        return $this->fields[$name];
    }

    /**
     *
     * Allows write access to related fields as properties.
     *
     * @param string $name The related field name.
     *
     * @param mixed $value The field value.
     *
     */
    public function __set(string $name, $value) : void
    {
        $this->assertHas($name);
        $this->modify($name, $value);
    }

    /**
     *
     * Allows isset() access to related fields as properties.
     *
     * @param string $name The related field name.
     *
     * @return bool
     *
     */
    public function __isset(string $name) : bool
    {
        $this->assertHas($name);
        return isset($this->fields[$name]);
    }

    /**
     *
     * Allows unset() access to related fields as properties; note that this
     * sets the value to null, and does not remove the related field.
     *
     * @param string $name The related field name.
     *
     */
    public function __unset(string $name) : void
    {
        $this->assertHas($name);
        $this->fields[$name] = null;
    }

    /**
     *
     * Gets the array of related fields.
     *
     * @return array
     *
     */
    public function getFields() : array
    {
        return $this->fields;
    }

    /**
     *
     * Sets multiple field values at once.
     *
     * @param array $namesValues An array of key-value pairs where the key is
     * the field name and the value is the value to set.
     *
     */
    public function set(array $namesValues = []) : void
    {
        foreach ($namesValues as $name => $value) {
            if ($this->has($name)) {
                $this->modify($name, $value);
            }
        }
    }

    /**
     *
     * Does a related field exist?
     *
     * @param string $name The related field name.
     *
     * @return bool
     *
     */
    public function has($name) : bool
    {
        return array_key_exists($name, $this->fields);
    }

    /**
     *
     * Returns an array copy of the related Record and RecordSet objects.
     *
     * @return array
     *
     */
    public function getArrayCopy() : array
    {
        $array = [];
        foreach ($this->fields as $name => $foreign) {
            $array[$name] = $foreign;
            if ($foreign) {
                $array[$name] = $foreign->getArrayCopy();
            }
        }
        return $array;
    }

    /**
     *
     * Modifies a field, making sure the new value is of an allowed type.
     *
     * @param string $name The field name to modify.
     *
     * @param mixed $value The new field value. Must be null, false, an empty
     * array, a RecordInterface, or a RecordSetInterface.
     *
     * @throws Exception when the new value is not of the expected type.
     *
     */
    protected function modify(string $name, $value) : void
    {
        $valid = $value === null
              || $value === false
              || $value === []
              || $value instanceof RecordInterface
              || $value instanceof RecordSetInterface;

        if (! $valid) {
            $expect = 'null, false, empty array, RecordInterface, or RecordSetInterface';
            throw Exception::invalidType($expect, $value);
        }

        $this->fields[$name] = $value;
    }

    /**
     *
     * Asserts that a field name exists.
     *
     * @param string $name The related field name.
     *
     * @throws Exception when the field name does not exist.
     *
     */
    protected function assertHas($name) : void
    {
        if (! $this->has($name)) {
            throw Exception::propertyDoesNotExist($this, $name);
        }
    }
}
