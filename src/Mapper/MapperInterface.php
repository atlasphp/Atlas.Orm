<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Exception;
use Atlas\Orm\Relation\ManyToMany;
use Atlas\Orm\Relation\ManyToOne;
use Atlas\Orm\Relation\OneToMany;
use Atlas\Orm\Relation\OneToOne;
use Atlas\Orm\Mapper\IdentityMap;
use Atlas\Orm\Mapper\Row;
use Atlas\Orm\Mapper\Primary;
use Atlas\Orm\Mapper\TableInterface;
use Aura\Sql\ConnectionLocator;
use Aura\SqlQuery\QueryFactory;

/**
 *
 * A data source mapper that returns Record and RecordSet objects.
 *
 * @package Atlas.Atlas
 *
 */
interface MapperInterface
{
    static public function getTableClass();

    public function getTable();

    public function getReadConnection();

    public function getWriteConnection();

    public function fetchRecord($primaryVal, array $with = []);

    public function fetchRecordBy(array $colsVals = [], array $with = []);

    public function fetchRecordSet(array $primaryVals, array $with = []);

    public function fetchRecordSetBy(array $colsVals = [], array $with = []);

    public function select(array $colsVals = []);

    public function insert(Record $record);

    public function update(Record $record);

    public function delete(Record $record);

    public function newRecord(array $cols = []);

    public function newRecordSet(array $records = [], array $with = []);

    public function getSelectedRecord(array $cols, array $with = []);

    public function getSelectedRecordSet(array $data, array $with = []);
}
