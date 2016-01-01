<?php
namespace Atlas\Orm\Table;

use Atlas\Orm\Exception;
use Aura\Sql\ConnectionLocator;
use Aura\SqlQuery\QueryFactory;
use Aura\SqlQuery\Common\SelectInterface;

interface GatewayInterface
{
    public function getTable();

    /**
     *
     * Returns the database read connection.
     *
     * @return ExtendedPdoInterface
     *
     */
    public function getReadConnection();

    /**
     *
     * Returns the database write connection.
     *
     * @return ExtendedPdoInterface
     *
     */
    public function getWriteConnection();

    public function fetchRow($primaryVal);

    public function fetchRows(array $primaryVals);

    public function select(array $colsVals = []);

    public function selectRow(GatewaySelect $select);

    public function selectRows(GatewaySelect $select);

    public function insert(RowInterface $row, callable $modify, callable $after);

    public function update(RowInterface $row, callable $modify, callable $after);

    public function delete(RowInterface $row, callable $modify, callable $after);

    /**
     *
     * Returns a new Row for the table.
     *
     * @return RowInterface
     *
     */
    public function newRow(array $cols = []);

    public function newSelectedRow(array $cols);

    public function getSelectedRow(array $cols);
}
