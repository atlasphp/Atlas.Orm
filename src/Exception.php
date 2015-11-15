<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @package Atlas.Atlas
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Orm;

use InvalidArgumentException;
use UnexpectedValueException;

/**
 *
 * Base exception class, with factory methods.
 *
 * @package Atlas.Atlas
 *
 */
class Exception extends \Exception
{
    public static function classDoesNotExist($class)
    {
        return new Exception("{$class} does not exist.");
    }

    public static function priorTransaction()
    {
        return new Exception("Cannot re-execute a prior transaction.");
    }

    public static function priorWork()
    {
        return new Exception("Cannot re-invoke prior work.");
    }

    public static function propertyDoesNotExist($class, $property)
    {
        return new Exception("{$class}::\${$property} does not exist.");
    }

    public static function immutableOnceSet($class, $property)
    {
        return new Exception("{$class}::\${$property} is immutable once set.");
    }

    public static function mapperNotFound($class)
    {
        return new Exception("{$class} not found in mapper locator.");
    }

    public static function tableNotFound($class)
    {
        return new Exception("{$class} not found in table locator.");
    }

    public static function invalidType($expect, $actual)
    {
        if (is_object($actual)) {
            $actual = get_class($actual);
        } else {
            $actual = gettype($actual);
        }

        return new InvalidArgumentException("Expected object of type '$expect', got '$actual' instead.");
    }

    public static function rowNotMapped()
    {
        return new Exception("Row does not exist in IdentityMap.");
    }

    public static function rowAlreadyMapped()
    {
        return new Exception("Row already exists in IdentityMap.");
    }

    public static function relationDoesNotExist($name)
    {
        return new Exception("Relation '$name' does not exist.");
    }

    public static function throughRelationNotFetched($name, $throughName)
    {
        return new Exception("Cannot fetch '{$name}' relation without '{$throughName}' relation.");
    }

    public static function unexpectedRowCountAffected($count)
    {
        return new UnexpectedValueException("Expected 1 row affected, actual {$count}");
    }
}
