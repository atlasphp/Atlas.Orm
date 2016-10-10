<?php

namespace Core\Database;

use App;
use ArrayAccess;
use ArrayIterator;
use Aura\Sql\Query\Select;
use Countable;
use DateTime;
use Harbor\Collections\Collection;
use IteratorAggregate;
use JsonSerializable;
use PDOException;
use Stringy\Stringy;

abstract class AbstractModel implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * @var array The Table Schema
     */
    protected static $schema = [];

    /**
     * @var string The Table Name
     */
    protected static $tableName = null;

    /**
     * @var \Aura\Sql\Connection\Mysql The database connection
     */
    protected static $connection = null;

    /**
     * @var array Holds relation methods
     */
    protected static $relations = [];

    /**
     * @var array Holds any relations to eager load automatically
     */
    protected static $with = [];

    /**
     * @var array The model data.
     */
    protected $modelData = [];

    /**
     * @var array Stores the original data.
     */
    protected $originalData = [];

    /**
     * @var array Keeps track of the modified properties.
     */
    protected $modifiedProperties = [];

    /**
     * @var array Any properties to exclude when toArray is called.
     */
    protected $hiddenProperties = [];

    /**
     * @var array Extra parameters
     */
    protected static $extraProperties = [];

    /**
     * @var array If set, only these fields will be included when
     *      toIndexedArray is called.
     */
    protected $indexFields = [];

    /**
     * @var bool Keeps track if the model is modified.
     */
    protected $isModified = false;

    /**
     * @var bool Keeps track if the model is a new entry or not.
     */
    protected $isNew = true;

    /**
     * @var \Aura\Sql\Connection\Mysql The database connection.
     */
    protected $db = null;

    /**
     * @var Framework
     */
    protected $app;

    /**
     * @var \Core\Config\Provider
     */
    protected $config;

    /**
     * @var \Core\ApplicationKernel
     */
    protected $kernel;

    public function __construct()
    {
        $this->db     = static::$connection;
        $this->app    = app();
        $this->kernel = App::get('app');
        $this->config = $this->kernel->get('config');
    }

    /**
     * Sets the DB Connection
     * @param $connection \Aura\Sql\Connection\Mysql
     */
    public static function setConnection($connection)
    {
        static::$connection = $connection;
    }

    /**
     * Gets the Table name for the model.
     * @return string
     */
    public static function getTableName()
    {
        if (is_null(static::$tableName)) {
            throw new \RuntimeException('$tableName not set in '.get_called_class());
        }

        return static::$tableName;
    }

    /**
     * Gets the Primary Key column name.
     * @return string
     */
    public static function getPrimaryKey()
    {
        return 'id';
    }

    /**
     * Makes a new Model instance then saves it (by default).
     * @param array $data
     * @param bool  $autoSave
     * @return static
     */
    public static function make($data = [], $autoSave = true)
    {
        $model = static::newInstance($data, true);

        if ($autoSave) {
            $result = $model->save();

            if (! $result) {
                return false;
            }
        }

        return $model;
    }

    /**
     * Find a row or make it.
     * @param       $column
     * @param array $data
     * @return AbstractModel|null
     */
    public static function findOrMakeBy($column, array $data)
    {
        if (! isset($data[$column])) {
            throw new \InvalidArgumentException("Column '$column' could not be found in the data you sent.");
        }

        $result = static::findOneBy($column, $data[$column]);

        if (! $result) {
            $result = static::make($data);
        }

        return $result;
    }

    /**
     * Finds an entry by the Primary Key
     * @param  mixed $value
     * @return AbstractModel|null
     */
    public static function findByPk($value)
    {
        $pk = static::getPrimaryKey();

        return static::findOne(function (Select $stmt) use ($pk) {
            if (! is_array($pk)) {
                $stmt->where("`$pk` = :value");
            } else {
                foreach ($pk as $key) {
                    $stmt->where("`$key` = :$key");
                }
            }
        }, is_array($value) ? $value : ['value' => $value]);
    }

    /**
     * Gets all rows, optionally modified using the $callback.
     * @param callable $callback
     * @param array    $bindData
     * @param null     $limit
     * @param int      $offset
     * @return Collection|null
     */
    public static function findAll(callable $callback = null, $bindData = [], $limit = null, $offset = 0)
    {
        $stmt = static::$connection->newSelect();
        $stmt->cols(['*'])
             ->from(static::getTableName());

        if (! is_null($limit)) {
            $stmt->limit($limit);
        }

        $stmt->offset($offset);

        if ($callback) {
            $return = call_user_func($callback, $stmt);
            if (! is_null($return)) {
                $stmt = $return;
            }
        }

        $results = static::$connection->fetchAll($stmt, $bindData);

        if ($results) {
            return static::resultsToCollection($results);
        }

        return null;
    }

    /**
     * Gets the column values, optionally modified using the $callback.
     * @param          $column
     * @param callable $callback
     * @param array    $bindData
     * @param null     $limit
     * @param int      $offset
     * @return Collection|null
     */
    public static function findCol($column, callable $callback = null, $bindData = [], $limit = null, $offset = 0)
    {
        $stmt = static::$connection->newSelect();
        $stmt->cols([$column])
             ->from(static::getTableName());

        if (! is_null($limit)) {
            $stmt->limit($limit);
        }

        $stmt->offset($offset);

        if ($callback) {
            $return = call_user_func($callback, $stmt);
            if (! is_null($return)) {
                $stmt = $return;
            }
        }

        $results = static::$connection->fetchCol($stmt, $bindData);

        return $results ?: [];
    }

    /**
     * Gets one row, optionally modified using the $callback.
     * @param callable $callback
     * @param array    $bindData
     * @return AbstractModel|null
     */
    public static function findOne(callable $callback = null, array $bindData = [])
    {
        $stmt = static::$connection->newSelect();
        $stmt->cols(['*'])
             ->from(static::getTableName());
        if ($callback) {
            $return = call_user_func($callback, $stmt);
            if (! is_null($return)) {
                $stmt = $return;
            }
        }

        $results = static::$connection->fetchOne($stmt, $bindData);

        if ($results) {
            return static::newInstance($results);
        }

        return null;
    }

    /**
     * Counts all rows, optionally modified using the $callback.
     * @param callable $callback
     * @param array    $bindData
     * @return int
     */
    public static function countAll(callable $callback = null, $bindData = [])
    {
        $stmt = static::$connection->newSelect();
        $stmt->cols(['COUNT(*) as cnt'])
             ->from(static::getTableName());
        if ($callback) {
            $return = call_user_func($callback, $stmt);
            if (! is_null($return)) {
                $stmt = $return;
            }
        }

        $results = static::$connection->fetchOne($stmt, $bindData);

        return (int) $results['cnt'];
    }

    /**
     * Finds multiple record where $column = $value.
     * @param      $column
     * @param      $value
     * @param null $limit
     * @param int  $offset
     * @return AbstractModel|null
     */
    public static function findBy($column, $value, $limit = null, $offset = 0)
    {
        return static::findAll(function (Select $stmt) use ($column, $value) {
            if (is_array($value)) {
                $stmt->where("`$column` IN (:val)");
            } else {
                $stmt->where("`$column` = :val");
            }

        }, ['val' => $value], $limit, $offset);
    }

    /**
     * Finds one record where $column = $value.
     * @param $column
     * @param $value
     * @return AbstractModel|null
     */
    public static function findOneBy($column, $value)
    {
        return static::findOne(function (Select $stmt) use ($column) {
            $stmt->where("`$column` = :val");
        }, ['val' => $value]);
    }

    /**
     * Magic Method to provide `findBy*` and `findOneBy*` methods.
     * @param $method
     * @param $args
     * @return AbstractModel|null
     */
    public static function __callStatic($method, $args)
    {
        $method = Stringy::create($method);
        if ($method->startsWith('findBy')) {
            $column = (string) $method->removeLeft('findBy')->underscored();

            return static::findBy($column, reset($args));
        } elseif ($method->startsWith('findOneBy')) {
            $column = (string) $method->removeLeft('findOneBy')->underscored();

            return static::findOneBy($column, reset($args));
        } elseif ($method->startsWith('findOrMakeBy')) {
            $column = (string) $method->removeLeft('findOrMakeBy')->underscored();

            return static::findOrMakeBy($column, reset($args));
        }

        throw new \BadMethodCallException('Method "'.$method.'" does not exist.');
    }

    /**
     * Creates a new Model instance and hydrates it.
     * @param array $data
     * @param bool  $isNew
     * @return static
     */
    public static function newInstance(array $data, $isNew = false)
    {
        return (new static())->hydrate($data, $isNew)->with(...static::$with);
    }

    /**
     * Converts the given results array to Model instances.
     * @param array|null $results
     * @param bool       $isNew
     * @return Collection
     */
    public static function resultsToCollection($results, $isNew = false)
    {
        $collection = new Collection();

        if ($results) {
            foreach ($results as $row) {
                $collection->push(static::newInstance($row, $isNew));
            }
        }

        return $collection;
    }

    /**
     * Converts the given results array to a keyed collection of Model
     * instances.
     * @param string     $key
     * @param array|null $results
     * @param bool       $isNew
     * @return Collection
     * @throws \InvalidArgumentException
     */
    protected static function resultsToKeyedCollection($key, $results, $isNew = false)
    {
        $collection = new Collection();

        if ($results) {
            foreach ($results as $row) {
                if (! isset($row[$key])) {
                    throw new \InvalidArgumentException("$key is not present");
                }

                $collection->set($row[$key], static::newInstance($row, $isNew));
            }
        }

        return $collection;
    }

    /**
     * Hydrates the Model with the given data.
     * @param array $data
     * @param bool  $isNew
     * @return $this
     */
    public function hydrate(array $data, $isNew = true)
    {
        $this->modelData = $data;

        $cols = array_merge(static::$schema, static::$extraProperties);
        foreach ($cols as $col => $details) {
            if (isset($this->modelData[$col])) {
                if ($details['type'] === 'json') {
                    $this->modelData[$col] = is_array($this->modelData[$col]) ? $this->modelData[$col] : json_decode($this->modelData[$col], true);
                } elseif ($details['type'] === 'datetime') {
                    $this->modelData[$col] = DateTime::createFromFormat('Y-m-d H:i:s', $this->modelData[$col]);
                } elseif (! is_null($details['type'])) {
                    settype($this->modelData[$col], isset($details['type']) ? $details['type'] : 'string');
                }
            } else {
                $this->modelData[$col] = isset($details['default']) ? $details['default'] : null;
            }
        }

        $this->setNew($isNew);

        return $this;
    }

    /**
     * Checks if the Model is modified.
     * @return bool
     */
    public function isModified()
    {
        return $this->isModified;
    }

    /**
     * Checks if the given property has been modified.
     * @param  string $property
     * @return bool
     */
    public function isPropertyModified($property)
    {
        return in_array($property, $this->modifiedProperties);
    }

    /**
     * Sets if the Model is modified.
     * @param bool $isModified
     */
    public function setModified($isModified = true)
    {
        $this->isModified = $isModified;
    }

    /**
     * Gets the original value of the property.
     * @param $property
     * @return mixed
     */
    public function getOriginalValue($property)
    {
        return isset($this->originalData[$property]) ? $this->originalData[$property] : $this->get($property);
    }

    /**
     * Checks if the Model represents a new record.
     * @return bool
     */
    public function isNew()
    {
        return $this->isNew;
    }

    /**
     * Sets if the Model is marked as New or not.
     * @param bool $isNew
     */
    public function setNew($isNew = true)
    {
        $this->isNew = $isNew;
    }

    /**
     * Gets the data to save in the database.
     * @return array
     */
    public function getDataForSave()
    {
        $data = array_intersect_key($this->modelData, static::$schema);

        foreach ($data as $col => $value) {

            // If the column contains JSON data, make sure it is encoded
            if (static::$schema[$col]['type'] === 'json' && is_array($value)) {
                $data[$col] = json_encode($value);
            } elseif (static::$schema[$col]['type'] === 'datetime' && $value instanceof DateTime) {
                $data[$col] = $value->format('Y-m-d H:i:s');
            }
        }

        return $data;
    }

    /**
     * Updates the Primary Key value of the model without marking it as
     * Modified.
     * @param $value
     */
    public function updatePrimaryKey($value)
    {
        $pk = static::getPrimaryKey();

        if (is_array($pk)) {
            return; // NOOP because when the PK is an array, all the values must be set already
        }

        if (isset(static::$schema[$pk])) {
            settype($value, isset(static::$schema[$pk]['type']) ? static::$schema[$pk]['type'] : 'int');
        }
        $this->set($pk, $value, false);
    }

    /**
     * Checks if the Model has the given property.
     * @param $property
     * @return bool
     */
    public function has($property)
    {
        $default = '__default_'.time();

        return $this->get($property, $default) !== $default;
    }

    /**
     * Sets the specified property on the Model.
     * @param string $property
     * @param mixed  $value
     * @param bool   $setModified
     * @return $this
     */
    public function set($property, $value, $setModified = true)
    {
        $modified = true;
        if (is_null($property)) {
            $this->modelData[] = $value;
        } elseif (isset(static::$extraProperties[$property])) {
            $this->modelData[$property] = $value;
            $modified = false;
        } elseif (! isset($this->modelData[$property]) || (isset($this->modelData[$property]) && $this->modelData[$property] != $value)) {
            $this->originalData[$property] = $this->modelData[$property];
            $this->modelData[$property]    = $value;
        } elseif (strpos($property, '/') !== false) {
            $path     = $property;
            $property = explode('/', $property)[0];

            if (isset($this->modelData[$property])) {
                array_set($this->modelData, $path, $value);
                $this->originalData[$property] = $this->modelData[$property];
            } else {
                $modified = false;
            }
        } else {
            $modified = false;
        }

        if ($modified && $setModified && ! $this->isPropertyModified($property)) {
            $this->modifiedProperties[] = $property;
        }
        if (! $this->isModified()) {
            $this->setModified($modified && $setModified);
        }

        return $this;
    }

    /**
     * Gets the specified property from the Model.
     * @param string $property
     * @param null   $default
     * @return mixed
     */
    public function get($property, $default = null)
    {
        if (isset($this->modelData[$property])) {
            return $this->modelData[$property];
        } elseif (isset(static::$relations[$property])) {
            $this->modelData[$property] = $this->{static::$relations[$property]}();

            return $this->modelData[$property];
        }

        return $default;
    }

    /**
     * Load the relations.
     * @param $relations
     * @return $this
     */
    public function with(...$relations)
    {
        foreach ($relations as $relation) {
            if (isset($this->modelData[$relation])) {
                continue;
            }

            if (! isset(static::$relations[$relation])) {
                throw new \InvalidArgumentException("$relation is not a valid relation.");
            }

            $this->modelData[$relation] = $this->{static::$relations[$relation]}();
        }

        return $this;
    }

    /**
     * Returns an array representation of the Model.
     * @return array
     */
    public function toArray()
    {
        $arr = [];
        foreach ($this->modelData as $key => $val) {
            if (in_array($key, $this->hiddenProperties)) {
                continue;
            }
            if (is_array($val)) {
                $iArr = [];
                foreach ($val as $iKey => $iValue) {
                    if (is_object($iValue) && method_exists($iValue, 'toArray')) {
                        $iArr[$iKey] = $iValue->toArray();
                    } else {
                        $iArr[$iKey] = $iValue;
                    }
                }
                $arr[$key] = $iArr;
            } elseif (is_object($val) && method_exists($val, 'toArray')) {
                $arr[$key] = $val->toArray();
            } else {
                $arr[$key] = $val;
            }
        }

        return $arr;
    }

    /**
     * Generates an index array of the fields to be indexed.
     * @return array
     */
    public function toIndexArray()
    {
        $arr = [];
        foreach ($this->modelData as $key => $val) {
            if (in_array($key, $this->hiddenProperties)) {
                continue;
            }
            if (is_array($val)) {
                $iArr = [];
                foreach ($val as $iKey => $iValue) {
                    if (is_object($iValue) && method_exists($iValue, 'toIndexArray')) {
                        $iArr[$iKey] = $iValue->toIndexArray();
                    } elseif (is_object($iValue) && method_exists($iValue, 'toArray')) {
                        $iArr[$iKey] = $iValue->toArray();
                    } else {
                        $iArr[$iKey] = $iValue;
                    }
                }
                $arr[$key] = $iArr;
            } elseif (is_object($val) && method_exists($val, 'toIndexArray')) {
                $arr[$key] = $val->toIndexArray();
            } elseif (is_object($val) && method_exists($val, 'toArray')) {
                $arr[$key] = $val->toArray();
            } else {
                $arr[$key] = $val;
            }
        }

        if (empty($this->indexFields)) {
            return $arr;
        }

        return array_intersect_key($arr, array_flip($this->indexFields));
    }

    /**
     * Returns a JSON representation of the Model.
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * Saves the Model.  Returns FALSE when the model does not need to be
     * saved.
     * On INSERTs it ill return the insert ID.  On UPDATEs it will return the
     * affected rows.
     * @param bool $markModified
     * @return bool|int
     */
    public function save($markModified = true)
    {
        if (! $this->isNew() && ! $this->isModified()) {
            return false;
        }
        $now = time();
        $pk   = static::getPrimaryKey();

        if ($this->isNew()) {
            if (! isset($this->modelData['created']) && isset(static::$schema['created']) && ! $this->isPropertyModified('created')) {
                $this->created = $now;
            }

            if (! isset($this->modelData['modified']) && isset(static::$schema['modified']) && ! $this->isPropertyModified('modified')) {
                $this->modified = $now;
            }

            $modelData = $this->getDataForSave();
            $stmt      = $this->db->newInsert()
                                  ->into($this->getTableName())
                                  ->cols(array_keys($modelData));

            try {
                $this->db->query($stmt, $modelData);
            } catch (PDOException $e) {
                if ($e->errorInfo[1] != 1062) {
                    throw $e;
                }

                return false;
            }

            $this->resetMetadata();

            if (! is_array($pk)) {
                $lastId = $this->db->lastInsertId();
                $this->updatePrimaryKey($lastId);

                return $lastId;
            }

            return true;
        }

        if ($markModified && isset(static::$schema['modified']) && ! $this->isPropertyModified('modified')) {
            $this->modified = $now;
        }

        $modelData = $this->getDataForSave();

        $stmt = $this->db->newUpdate()
                         ->table($this->getTableName())
                         ->cols(array_keys($modelData));

        if (! is_array($pk)) {
            $stmt->where("{$pk} = :{$pk}");
        } else {
            foreach ($pk as $key) {
                $stmt->where("{$key} = :{$key}");
            }
        }
        $result = $this->db->query($stmt, $modelData);

        $this->resetMetadata();

        return $result->rowCount();
    }

    /**
     * Deletes the current model from the database.
     * @return int|bool
     */
    public function delete()
    {
        if ($this->isNew()) {
            return false;
        }

        $pk   = static::getPrimaryKey();
        $stmt = $this->db->newDelete()
                         ->from(static::getTableName());

        if (! is_array($pk)) {
            $stmt->where("{$pk} = :{$pk}");
            $result = $this->db->query($stmt, [
                $pk => $this->get($pk),
            ]);
        } else {
            $bind = [];

            foreach ($pk as $key) {
                $stmt->where("{$key} = :{$key}");
                $bind[$key] = $this->get($key);
            }

            $result = $this->db->query($stmt, $bind);
        }

        return $result->rowCount();
    }

    /****************************************************************/
    /* Interface Implementations and PHP Magic Methods              */
    /****************************************************************/

    public function __get($property)
    {
        return $this->get($property);
    }

    public function __set($property, $value)
    {
        $this->set($property, $value);
    }

    public function __isset($property)
    {
        return $this->has($property);
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->modelData);
    }

    public function count()
    {
        return count($this->modelData);
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetUnset($offset)
    {
        unset($this->modelData[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    protected function resetMetadata()
    {
        $this->setNew(false);
        $this->setModified(false);
        $this->modifiedProperties = [];
    }
}