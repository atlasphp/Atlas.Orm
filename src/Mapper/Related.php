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
        $this->fields = $fields;
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
    public function __get($name)
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
    public function __set($name, $value)
    {
        $this->assertHas($name);
        $this->fields[$name] = $value;
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
    public function __isset($name)
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
    public function __unset($name)
    {
        $this->assertHas($name);
        $this->fields[$name] = null;
    }

    /**
     *
     * Sets multiple field values at once.
     *
     * @param array $namesValues An array of key-value pairs where the key is
     * the field name and the value is the value to set.
     *
     */
    public function set(array $namesValues = [])
    {
        foreach ($namesValues as $name => $value) {
            $this->assertHas($name);
            $this->related[$name] = $value;
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
    public function has($name)
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
    public function getArrayCopy()
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
     * Asserts that a field name exists.
     *
     * @param string $name The related field name.
     *
     * @throws Exception when the field name does not exist.
     *
     */
    protected function assertHas($name)
    {
        if (! $this->has($name)) {
            throw Exception::propertyDoesNotExist($this, $name);
        }
    }
}
