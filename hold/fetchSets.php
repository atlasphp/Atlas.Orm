<?php

    /*
    Rows by primary:
        create empty rows
        foreach primary value ...
            add null in rows keyed on primary value to maintain place
            if primary value in map
                retain mapped row in set keyed on primary value
                remove primary value from list
        select remaining primary values
        foreach returned one ...
            new row object
            retain row in map
            add row in set on ID key
        return rows
    */
    public function fetchRows($primaryVals)
    {
        // pre-empt working on empty array
        if (! $primaryVals) {
            return array();
        }

        $rows = [];
        $this->fillExistingRows($primaryVals, $rows);
        $this->fillMissingRows($primaryVals, $rows);

        // remove unfound rows
        foreach ($rows as $key => $val) {
            if ($val === null) {
                unset($rows[$key]);
            }
        }

        // done
        return array_values($rows);
    }

    // get existing rows from identity map
    protected function fillExistingRows(&$primaryVals, &$rows)
    {
        foreach ($primaryVals as $i => $primaryVal) {
            $rows[$primaryVal] = null;
            if ($this->identityMap->hasPrimaryVal($primaryVal)) {
                $rows[$primaryVal] = $this->identityMap->getRow($primaryVal);
                unset($primaryVals[$i]);
            }
        }
    }

    // get missing rows from database
    protected function fillMissingRows(&$primaryVals, &$rows)
    {
        // are there still rows to fetch?
        if (! $primaryVals) {
            return;
        }
        // fetch and retain remaining rows
        $colsVals = [$this->getPrimary() => $primaryVals];
        $select = $this->select($colsVals);
        $data = $select->cols($this->getCols())->fetchAll();
        foreach ($data as $cols) {
            $row = $this->newRow($cols);
            $this->identityMap->set($row);
            $rows[$row->getPrimaryVal()] = $row;
        }
    }

    public function fetchRowsBy(array $colsVals, callable $custom = null)
    {
        $select = $this->select($colsVals, $custom);
        return $this->fetchRowsBySelect($select);
    }

    public function fetchRowsBySelect(TableSelect $select)
    {
        $data = $select->cols($this->getCols())->fetchAll();
        if (! $data) {
            return array();
        }

        $rows = [];
        foreach ($data as $cols) {
            $rows[] = $this->getMappedOrNewRow($cols);
        }

        return $rows;
    }

    public function fetchRowSets($primaryVals, $col)
    {
        $rows = $this->fetchRows($primaryVals);
        $groups = [];
        foreach ($rows as $row) {
            $groups[$row->$col][] = $row;
        }
        return $this->rowSetsFromGroups($groups);
    }

    public function fetchRowSetsBy(array $colsVals, $col)
    {
        $select = $this->select($colsVals);
        return $this->fetchRowSetsBySelect($select, $col);
    }

    public function fetchRowSetsBySelect(TableSelect $select, $col)
    {
        $data = $select->cols($this->getCols())->fetchAll();
        $groups = [];
        foreach ($data as $cols) {
            $row = $this->getMappedOrNewRow($cols);
            $groups[$row->$col][] = $row;
        }
        return $this->rowSetsFromGroups($groups);
    }

    protected function rowSetsFromGroups($groups)
    {
        $rowSets = [];
        foreach ($groups as $key => $rows) {
            $rowSets[$key] = $this->newRowSet($rows);
        }
        return $rowSets;
    }

    // -----

    public function testFetchRows()
    {
        $expect = [
            [
                'id' => '1',
                'name' => 'Anna',
                'building' => '1',
                'floor' => '1',
            ],
            [
                'id' => '2',
                'name' => 'Betty',
                'building' => '1',
                'floor' => '2',
            ],
            [
                'id' => '3',
                'name' => 'Clara',
                'building' => '1',
                'floor' => '3',
            ],
        ];

        $actual = $this->table->fetchRows([1, 2, 3]);
        $this->assertCount(3, $actual);
        $this->assertInstanceOf(Row::CLASS, $actual[0]);
        $this->assertInstanceOf(Row::CLASS, $actual[1]);
        $this->assertInstanceOf(Row::CLASS, $actual[2]);
        $this->assertSame($expect[0], $actual[0]->getArrayCopy());
        $this->assertSame($expect[1], $actual[1]->getArrayCopy());
        $this->assertSame($expect[2], $actual[2]->getArrayCopy());

        $again = $this->table->fetchRows([1, 2, 3]);
        $this->assertCount(3, $again);
        $this->assertInstanceOf(Row::CLASS, $again[0]);
        $this->assertInstanceOf(Row::CLASS, $again[1]);
        $this->assertInstanceOf(Row::CLASS, $again[2]);
        $this->assertSame($actual[0], $again[0]);
        $this->assertSame($actual[1], $again[1]);
        $this->assertSame($actual[2], $again[2]);

        $actual = $this->table->fetchRows([997, 998, 999]);
        $this->assertSame(array(), $actual);
    }

    public function testFetchRowsBy()
    {
        $expect = array (
            array (
                'id' => '1',
                'name' => 'Anna',
                'building' => '1',
                'floor' => '1',
            ),
            array (
                'id' => '2',
                'name' => 'Betty',
                'building' => '1',
                'floor' => '2',
            ),
            array (
                'id' => '3',
                'name' => 'Clara',
                'building' => '1',
                'floor' => '3',
            ),
            array (
                'id' => '4',
                'name' => 'Donna',
                'building' => '1',
                'floor' => '1',
            ),
            array (
                'id' => '5',
                'name' => 'Edna',
                'building' => '1',
                'floor' => '2',
            ),
            array (
                'id' => '6',
                'name' => 'Fiona',
                'building' => '1',
                'floor' => '3',
            ),
        );

        $actual = $this->table->fetchRowsBy(['building' => '1']);
        $this->assertTrue(is_array($actual));
        $this->assertCount(6, $actual);
        $this->assertInstanceOf(Row::CLASS, $actual[0]);
        $this->assertInstanceOf(Row::CLASS, $actual[1]);
        $this->assertInstanceOf(Row::CLASS, $actual[2]);
        $this->assertInstanceOf(Row::CLASS, $actual[3]);
        $this->assertInstanceOf(Row::CLASS, $actual[4]);
        $this->assertInstanceOf(Row::CLASS, $actual[5]);
        $this->assertSame($expect[0], $actual[0]->getArrayCopy());
        $this->assertSame($expect[1], $actual[1]->getArrayCopy());
        $this->assertSame($expect[2], $actual[2]->getArrayCopy());
        $this->assertSame($expect[3], $actual[3]->getArrayCopy());
        $this->assertSame($expect[4], $actual[4]->getArrayCopy());
        $this->assertSame($expect[5], $actual[5]->getArrayCopy());

        $again = $this->table->fetchRowsBy(['building' => '1']);
        $this->assertCount(6, $again);
        $this->assertInstanceOf(Row::CLASS, $again[0]);
        $this->assertInstanceOf(Row::CLASS, $again[1]);
        $this->assertInstanceOf(Row::CLASS, $again[2]);
        $this->assertInstanceOf(Row::CLASS, $again[3]);
        $this->assertInstanceOf(Row::CLASS, $again[4]);
        $this->assertInstanceOf(Row::CLASS, $again[5]);
        $this->assertSame($actual[0], $again[0]);
        $this->assertSame($actual[1], $again[1]);
        $this->assertSame($actual[2], $again[2]);
        $this->assertSame($actual[3], $again[3]);
        $this->assertSame($actual[4], $again[4]);
        $this->assertSame($actual[5], $again[5]);

        $actual = $this->table->fetchRowsBy(['building' => '99']);
        $this->assertSame(array(), $actual);
    }

    public function testFetchRowSets()
    {
        $expect = [
            1 => [
                0 => [
                    'id' => '1',
                    'name' => 'Anna',
                    'building' => '1',
                    'floor' => '1',
                ],
                1 => [
                    'id' => '4',
                    'name' => 'Donna',
                    'building' => '1',
                    'floor' => '1',
                ],
            ],
            2 => [
                0 => [
                    'id' => '2',
                    'name' => 'Betty',
                    'building' => '1',
                    'floor' => '2',
                ],
                1 => [
                    'id' => '5',
                    'name' => 'Edna',
                    'building' => '1',
                    'floor' => '2',
                ],
            ],
            3 => [
                0 => [
                    'id' => '3',
                    'name' => 'Clara',
                    'building' => '1',
                    'floor' => '3',
                ],
                1 => [
                    'id' => '6',
                    'name' => 'Fiona',
                    'building' => '1',
                    'floor' => '3',
                ],
            ],
        ];

        $actualRowSets = $this->table->fetchRowSets([1, 2, 3, 4, 5, 6], 'floor');
        $this->assertTrue(is_array($actualRowSets));
        $this->assertCount(3, $actualRowSets);
        foreach ($actualRowSets as $floor => $actualRowSet) {
            $this->assertInstanceOf(RowSet::CLASS, $actualRowSet);
            $this->assertCount(2, $actualRowSet);
            $this->assertSame($expect[$floor][0], $actualRowSet[0]->getArrayCopy());
            $this->assertSame($expect[$floor][1], $actualRowSet[1]->getArrayCopy());
        }
    }

    public function testFetchRowSetsBy()
    {
        $expect = [
            1 => [
                0 => [
                    'id' => '1',
                    'name' => 'Anna',
                    'building' => '1',
                    'floor' => '1',
                ],
                1 => [
                    'id' => '4',
                    'name' => 'Donna',
                    'building' => '1',
                    'floor' => '1',
                ],
            ],
            2 => [
                0 => [
                    'id' => '2',
                    'name' => 'Betty',
                    'building' => '1',
                    'floor' => '2',
                ],
                1 => [
                    'id' => '5',
                    'name' => 'Edna',
                    'building' => '1',
                    'floor' => '2',
                ],
            ],
            3 => [
                0 => [
                    'id' => '3',
                    'name' => 'Clara',
                    'building' => '1',
                    'floor' => '3',
                ],
                1 => [
                    'id' => '6',
                    'name' => 'Fiona',
                    'building' => '1',
                    'floor' => '3',
                ],
            ],
        ];

        $actualRowSets = $this->table->fetchRowSetsBy(['id' => [1, 2, 3, 4, 5, 6]], 'floor');
        $this->assertTrue(is_array($actualRowSets));
        $this->assertCount(3, $actualRowSets);
        foreach ($actualRowSets as $floor => $actualRowSet) {
            $this->assertInstanceOf(RowSet::CLASS, $actualRowSet);
            $this->assertCount(2, $actualRowSet);
            $this->assertSame($expect[$floor][0], $actualRowSet[0]->getArrayCopy());
            $this->assertSame($expect[$floor][1], $actualRowSet[1]->getArrayCopy());
        }
    }

// =============================================================================

    public function fetchRecords($primaryVals)
    {
        $rows = $this->table->fetchRows($primaryVals);
        return $this->groupRecords($rows);
    }

    public function fetchRecordsBy($colsVals, callable $custom = null)
    {
        $rows = $this->table->fetchRowsBy($colsVals, $custom);
        return $this->groupRecords($rows);
    }

    public function fetchRecordsBySelect(TableSelect $tableSelect)
    {
        $rows = $this->table->fetchRowsBySelect($tableSelect);
        return $this->groupRecords($rows);
    }

    protected function groupRecords(array $rows)
    {
        $records = [];
        foreach ($rows as $key => $row) {
            $records[$key] = $this->newRecord($row);
        }
        return $records;
    }

    public function fetchRecordSets($primaryVals, $col)
    {
        $rowSets = $this->table->fetchRowSets($primaryVals, $col);
        return $this->groupRecordSets($rowSets);
    }

    public function fetchRecordSetsBy($colsVals, $col, callable $custom = null)
    {
        $rowSets = $this->table->fetchRowSetsBy($colsVals, $col, $custom);
        return $this->groupRecordSets($rowSets);
    }

    public function fetchRecordSetsBySelect(TableSelect $tableSelect, $col)
    {
        $rowSets = $this->table->fetchRowSetsBySelect($tableSelect, $col);
        return $this->groupRecordSets($rowSets);
    }

    protected function groupRecordSets(array $rowSets)
    {
        $recordSets = [];
        foreach ($rowSets as $key => $rowSet) {
            $recordSets[$key] = $this->newRecordSet($rowSet);
        }
        return $recordSets;
    }

    // -----

    public function testFetchRecords()
    {
        $expect = [
            [
                'id' => '1',
                'name' => 'Anna',
                'building' => '1',
                'floor' => '1',
            ],
            [
                'id' => '2',
                'name' => 'Betty',
                'building' => '1',
                'floor' => '2',
            ],
            [
                'id' => '3',
                'name' => 'Clara',
                'building' => '1',
                'floor' => '3',
            ],
        ];

        $actual = $this->mapper->fetchRecords([1, 2, 3]);
        $this->assertTrue(is_array($actual));
        $this->assertCount(3, $actual);
        $this->assertInstanceOf(Record::CLASS, $actual[0]);
        $this->assertInstanceOf(Record::CLASS, $actual[1]);
        $this->assertInstanceOf(Record::CLASS, $actual[2]);
        $this->assertSame($expect[0], $actual[0]->getRow()->getArrayCopy());
        $this->assertSame($expect[1], $actual[1]->getRow()->getArrayCopy());
        $this->assertSame($expect[2], $actual[2]->getRow()->getArrayCopy());

        $again = $this->mapper->fetchRecords([1, 2, 3]);
        $this->assertTrue(is_array($again));
        $this->assertCount(3, $again);
        $this->assertInstanceOf(Record::CLASS, $again[0]);
        $this->assertInstanceOf(Record::CLASS, $again[1]);
        $this->assertInstanceOf(Record::CLASS, $again[2]);
        $this->assertSame($actual[0]->getRow(), $again[0]->getRow());
        $this->assertSame($actual[1]->getRow(), $again[1]->getRow());
        $this->assertSame($actual[2]->getRow(), $again[2]->getRow());

        $actual = $this->mapper->fetchRecords([997, 998, 999]);
        $this->assertSame(array(), $actual);
    }

    public function testFetchRecordsBy()
    {
        $expect = array (
            array (
                'id' => '1',
                'name' => 'Anna',
                'building' => '1',
                'floor' => '1',
            ),
            array (
                'id' => '2',
                'name' => 'Betty',
                'building' => '1',
                'floor' => '2',
            ),
            array (
                'id' => '3',
                'name' => 'Clara',
                'building' => '1',
                'floor' => '3',
            ),
            array (
                'id' => '4',
                'name' => 'Donna',
                'building' => '1',
                'floor' => '1',
            ),
            array (
                'id' => '5',
                'name' => 'Edna',
                'building' => '1',
                'floor' => '2',
            ),
            array (
                'id' => '6',
                'name' => 'Fiona',
                'building' => '1',
                'floor' => '3',
            ),
        );

        $actual = $this->mapper->fetchRecordsBy(['building' => '1']);
        $this->assertTrue(is_array($actual));
        $this->assertCount(6, $actual);
        $this->assertInstanceOf(Record::CLASS, $actual[0]);
        $this->assertInstanceOf(Record::CLASS, $actual[1]);
        $this->assertInstanceOf(Record::CLASS, $actual[2]);
        $this->assertInstanceOf(Record::CLASS, $actual[3]);
        $this->assertInstanceOf(Record::CLASS, $actual[4]);
        $this->assertInstanceOf(Record::CLASS, $actual[5]);
        $this->assertSame($expect[0], $actual[0]->getRow()->getArrayCopy());
        $this->assertSame($expect[1], $actual[1]->getRow()->getArrayCopy());
        $this->assertSame($expect[2], $actual[2]->getRow()->getArrayCopy());
        $this->assertSame($expect[3], $actual[3]->getRow()->getArrayCopy());
        $this->assertSame($expect[4], $actual[4]->getRow()->getArrayCopy());
        $this->assertSame($expect[5], $actual[5]->getRow()->getArrayCopy());

        $again = $this->mapper->fetchRecordsBy(['building' => '1']);
        $this->assertTrue(is_array($again));
        $this->assertCount(6, $again);
        $this->assertInstanceOf(Record::CLASS, $again[0]);
        $this->assertInstanceOf(Record::CLASS, $again[1]);
        $this->assertInstanceOf(Record::CLASS, $again[2]);
        $this->assertInstanceOf(Record::CLASS, $again[3]);
        $this->assertInstanceOf(Record::CLASS, $again[4]);
        $this->assertInstanceOf(Record::CLASS, $again[5]);
        $this->assertSame($actual[0]->getRow(), $again[0]->getRow());
        $this->assertSame($actual[1]->getRow(), $again[1]->getRow());
        $this->assertSame($actual[2]->getRow(), $again[2]->getRow());
        $this->assertSame($actual[3]->getRow(), $again[3]->getRow());
        $this->assertSame($actual[4]->getRow(), $again[4]->getRow());
        $this->assertSame($actual[5]->getRow(), $again[5]->getRow());

        $actual = $this->mapper->fetchRecordsBy(['building' => '99']);
        $this->assertSame(array(), $actual);
    }

    public function testFetchRecordsBySelect()
    {
        $expect = array (
            array (
                'id' => '1',
                'name' => 'Anna',
                'building' => '1',
                'floor' => '1',
            ),
            array (
                'id' => '2',
                'name' => 'Betty',
                'building' => '1',
                'floor' => '2',
            ),
            array (
                'id' => '3',
                'name' => 'Clara',
                'building' => '1',
                'floor' => '3',
            ),
            array (
                'id' => '4',
                'name' => 'Donna',
                'building' => '1',
                'floor' => '1',
            ),
            array (
                'id' => '5',
                'name' => 'Edna',
                'building' => '1',
                'floor' => '2',
            ),
            array (
                'id' => '6',
                'name' => 'Fiona',
                'building' => '1',
                'floor' => '3',
            ),
        );

        $select = $this->mapper->select(['building' => '1']);
        $actual = $this->mapper->fetchRecordsBySelect($select);
        $this->assertTrue(is_array($actual));
        $this->assertCount(6, $actual);
        $this->assertInstanceOf(Record::CLASS, $actual[0]);
        $this->assertInstanceOf(Record::CLASS, $actual[1]);
        $this->assertInstanceOf(Record::CLASS, $actual[2]);
        $this->assertInstanceOf(Record::CLASS, $actual[3]);
        $this->assertInstanceOf(Record::CLASS, $actual[4]);
        $this->assertInstanceOf(Record::CLASS, $actual[5]);
        $this->assertSame($expect[0], $actual[0]->getRow()->getArrayCopy());
        $this->assertSame($expect[1], $actual[1]->getRow()->getArrayCopy());
        $this->assertSame($expect[2], $actual[2]->getRow()->getArrayCopy());
        $this->assertSame($expect[3], $actual[3]->getRow()->getArrayCopy());
        $this->assertSame($expect[4], $actual[4]->getRow()->getArrayCopy());
        $this->assertSame($expect[5], $actual[5]->getRow()->getArrayCopy());

        $again = $this->mapper->fetchRecordsBySelect($select);
        $this->assertTrue(is_array($again));
        $this->assertCount(6, $again);
        $this->assertInstanceOf(Record::CLASS, $again[0]);
        $this->assertInstanceOf(Record::CLASS, $again[1]);
        $this->assertInstanceOf(Record::CLASS, $again[2]);
        $this->assertInstanceOf(Record::CLASS, $again[3]);
        $this->assertInstanceOf(Record::CLASS, $again[4]);
        $this->assertInstanceOf(Record::CLASS, $again[5]);
        $this->assertSame($actual[0]->getRow(), $again[0]->getRow());
        $this->assertSame($actual[1]->getRow(), $again[1]->getRow());
        $this->assertSame($actual[2]->getRow(), $again[2]->getRow());
        $this->assertSame($actual[3]->getRow(), $again[3]->getRow());
        $this->assertSame($actual[4]->getRow(), $again[4]->getRow());
        $this->assertSame($actual[5]->getRow(), $again[5]->getRow());

        $select = $this->mapper->select(['building' => '99']);
        $actual = $this->mapper->fetchRecordsBySelect($select);
        $this->assertSame(array(), $actual);
    }

    public function testFetchRecordSets()
    {
        $expect = [
            1 => [
                0 => [
                    'id' => '1',
                    'name' => 'Anna',
                    'building' => '1',
                    'floor' => '1',
                ],
                1 => [
                    'id' => '4',
                    'name' => 'Donna',
                    'building' => '1',
                    'floor' => '1',
                ],
            ],
            2 => [
                0 => [
                    'id' => '2',
                    'name' => 'Betty',
                    'building' => '1',
                    'floor' => '2',
                ],
                1 => [
                    'id' => '5',
                    'name' => 'Edna',
                    'building' => '1',
                    'floor' => '2',
                ],
            ],
            3 => [
                0 => [
                    'id' => '3',
                    'name' => 'Clara',
                    'building' => '1',
                    'floor' => '3',
                ],
                1 => [
                    'id' => '6',
                    'name' => 'Fiona',
                    'building' => '1',
                    'floor' => '3',
                ],
            ],
        ];

        $actualRecordSets = $this->mapper->fetchRecordSets([1, 2, 3, 4, 5, 6], 'floor');
        $this->assertTrue(is_array($actualRecordSets));
        $this->assertCount(3, $actualRecordSets);
        foreach ($actualRecordSets as $floor => $actualRecordSet) {
            $this->assertInstanceOf(RecordSet::CLASS, $actualRecordSet);
            $this->assertCount(2, $actualRecordSet);
            $this->assertSame($expect[$floor][0], $actualRecordSet[0]->getRow()->getArrayCopy());
            $this->assertSame($expect[$floor][1], $actualRecordSet[1]->getRow()->getArrayCopy());
        }
    }

    public function testFetchRecordSetsBy()
    {
        $expect = [
            1 => [
                0 => [
                    'id' => '1',
                    'name' => 'Anna',
                    'building' => '1',
                    'floor' => '1',
                ],
                1 => [
                    'id' => '4',
                    'name' => 'Donna',
                    'building' => '1',
                    'floor' => '1',
                ],
            ],
            2 => [
                0 => [
                    'id' => '2',
                    'name' => 'Betty',
                    'building' => '1',
                    'floor' => '2',
                ],
                1 => [
                    'id' => '5',
                    'name' => 'Edna',
                    'building' => '1',
                    'floor' => '2',
                ],
            ],
            3 => [
                0 => [
                    'id' => '3',
                    'name' => 'Clara',
                    'building' => '1',
                    'floor' => '3',
                ],
                1 => [
                    'id' => '6',
                    'name' => 'Fiona',
                    'building' => '1',
                    'floor' => '3',
                ],
            ],
        ];

        $actualRecordSets = $this->mapper->fetchRecordSetsBy(['id' => [1, 2, 3, 4, 5, 6]], 'floor');
        $this->assertTrue(is_array($actualRecordSets));
        $this->assertCount(3, $actualRecordSets);
        foreach ($actualRecordSets as $floor => $actualRecordSet) {
            $this->assertInstanceOf(RecordSet::CLASS, $actualRecordSet);
            $this->assertCount(2, $actualRecordSet);
            $this->assertSame($expect[$floor][0], $actualRecordSet[0]->getRow()->getArrayCopy());
            $this->assertSame($expect[$floor][1], $actualRecordSet[1]->getRow()->getArrayCopy());
        }
    }

    public function testFetchRecordSetsBySelect()
    {
        $expect = [
            1 => [
                0 => [
                    'id' => '1',
                    'name' => 'Anna',
                    'building' => '1',
                    'floor' => '1',
                ],
                1 => [
                    'id' => '4',
                    'name' => 'Donna',
                    'building' => '1',
                    'floor' => '1',
                ],
            ],
            2 => [
                0 => [
                    'id' => '2',
                    'name' => 'Betty',
                    'building' => '1',
                    'floor' => '2',
                ],
                1 => [
                    'id' => '5',
                    'name' => 'Edna',
                    'building' => '1',
                    'floor' => '2',
                ],
            ],
            3 => [
                0 => [
                    'id' => '3',
                    'name' => 'Clara',
                    'building' => '1',
                    'floor' => '3',
                ],
                1 => [
                    'id' => '6',
                    'name' => 'Fiona',
                    'building' => '1',
                    'floor' => '3',
                ],
            ],
        ];

        $select = $this->mapper->select(['id' => [1, 2, 3, 4, 5, 6]]);
        $actualRecordSets = $this->mapper->fetchRecordSetsBySelect($select, 'floor');
        $this->assertTrue(is_array($actualRecordSets));
        $this->assertCount(3, $actualRecordSets);
        foreach ($actualRecordSets as $floor => $actualRecordSet) {
            $this->assertInstanceOf(RecordSet::CLASS, $actualRecordSet);
            $this->assertCount(2, $actualRecordSet);
            $this->assertSame($expect[$floor][0], $actualRecordSet[0]->getRow()->getArrayCopy());
            $this->assertSame($expect[$floor][1], $actualRecordSet[1]->getRow()->getArrayCopy());
        }
    }
