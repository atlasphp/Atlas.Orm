<?php
    // public function fetchRowSets($primaryVals, $col)
    // {
    //     $rows = $this->fetchRows($primaryVals);
    //     $groups = [];
    //     foreach ($rows as $row) {
    //         $groups[$row->$col][] = $row;
    //     }
    //     return $this->rowSetsFromGroups($groups);
    // }

    // public function fetchRowSetsBy(array $colsVals, $col)
    // {
    //     $select = $this->select($colsVals);
    //     return $this->fetchRowSetsBySelect($select, $col);
    // }

    // public function fetchRowSetsBySelect(TableSelect $select, $col)
    // {
    //     $data = $select->cols($this->getCols())->fetchAll();
    //     $groups = [];
    //     foreach ($data as $cols) {
    //         $row = $this->getMappedOrNewRow($cols);
    //         $groups[$row->$col][] = $row;
    //     }
    //     return $this->rowSetsFromGroups($groups);
    // }

    // protected function rowSetsFromGroups($groups)
    // {
    //     $rowSets = [];
    //     foreach ($groups as $key => $rows) {
    //         $rowSets[$key] = $this->newRowSet($rows);
    //     }
    //     return $rowSets;
    // }

    // -----

    // public function testFetchRowSets()
    // {
    //     $expect = [
    //         1 => [
    //             0 => [
    //                 'id' => '1',
    //                 'name' => 'Anna',
    //                 'building' => '1',
    //                 'floor' => '1',
    //             ],
    //             1 => [
    //                 'id' => '4',
    //                 'name' => 'Donna',
    //                 'building' => '1',
    //                 'floor' => '1',
    //             ],
    //         ],
    //         2 => [
    //             0 => [
    //                 'id' => '2',
    //                 'name' => 'Betty',
    //                 'building' => '1',
    //                 'floor' => '2',
    //             ],
    //             1 => [
    //                 'id' => '5',
    //                 'name' => 'Edna',
    //                 'building' => '1',
    //                 'floor' => '2',
    //             ],
    //         ],
    //         3 => [
    //             0 => [
    //                 'id' => '3',
    //                 'name' => 'Clara',
    //                 'building' => '1',
    //                 'floor' => '3',
    //             ],
    //             1 => [
    //                 'id' => '6',
    //                 'name' => 'Fiona',
    //                 'building' => '1',
    //                 'floor' => '3',
    //             ],
    //         ],
    //     ];

    //     $actualRowSets = $this->table->fetchRowSets([1, 2, 3, 4, 5, 6], 'floor');
    //     $this->assertTrue(is_array($actualRowSets));
    //     $this->assertCount(3, $actualRowSets);
    //     foreach ($actualRowSets as $floor => $actualRowSet) {
    //         $this->assertInstanceOf(RowSet::CLASS, $actualRowSet);
    //         $this->assertCount(2, $actualRowSet);
    //         $this->assertSame($expect[$floor][0], $actualRowSet[0]->getArrayCopy());
    //         $this->assertSame($expect[$floor][1], $actualRowSet[1]->getArrayCopy());
    //     }
    // }

    // public function testFetchRowSetsBy()
    // {
    //     $expect = [
    //         1 => [
    //             0 => [
    //                 'id' => '1',
    //                 'name' => 'Anna',
    //                 'building' => '1',
    //                 'floor' => '1',
    //             ],
    //             1 => [
    //                 'id' => '4',
    //                 'name' => 'Donna',
    //                 'building' => '1',
    //                 'floor' => '1',
    //             ],
    //         ],
    //         2 => [
    //             0 => [
    //                 'id' => '2',
    //                 'name' => 'Betty',
    //                 'building' => '1',
    //                 'floor' => '2',
    //             ],
    //             1 => [
    //                 'id' => '5',
    //                 'name' => 'Edna',
    //                 'building' => '1',
    //                 'floor' => '2',
    //             ],
    //         ],
    //         3 => [
    //             0 => [
    //                 'id' => '3',
    //                 'name' => 'Clara',
    //                 'building' => '1',
    //                 'floor' => '3',
    //             ],
    //             1 => [
    //                 'id' => '6',
    //                 'name' => 'Fiona',
    //                 'building' => '1',
    //                 'floor' => '3',
    //             ],
    //         ],
    //     ];

    //     $actualRowSets = $this->table->fetchRowSetsBy(['id' => [1, 2, 3, 4, 5, 6]], 'floor');
    //     $this->assertTrue(is_array($actualRowSets));
    //     $this->assertCount(3, $actualRowSets);
    //     foreach ($actualRowSets as $floor => $actualRowSet) {
    //         $this->assertInstanceOf(RowSet::CLASS, $actualRowSet);
    //         $this->assertCount(2, $actualRowSet);
    //         $this->assertSame($expect[$floor][0], $actualRowSet[0]->getArrayCopy());
    //         $this->assertSame($expect[$floor][1], $actualRowSet[1]->getArrayCopy());
    //     }
    // }

// =============================================================================

    // public function fetchRecordSets($primaryVals, $col)
    // {
    //     $rowSets = $this->table->fetchRowSets($primaryVals, $col);
    //     return $this->groupRecordSets($rowSets);
    // }

    // public function fetchRecordSetsBy($colsVals, $col, callable $custom = null)
    // {
    //     $rowSets = $this->table->fetchRowSetsBy($colsVals, $col, $custom);
    //     return $this->groupRecordSets($rowSets);
    // }

    // public function fetchRecordSetsBySelect(TableSelect $tableSelect, $col)
    // {
    //     $rowSets = $this->table->fetchRowSetsBySelect($tableSelect, $col);
    //     return $this->groupRecordSets($rowSets);
    // }

    // protected function groupRecordSets(array $rowSets)
    // {
    //     $recordSets = [];
    //     foreach ($rowSets as $key => $rowSet) {
    //         $recordSets[$key] = $this->newRecordSet($rowSet);
    //     }
    //     return $recordSets;
    // }

    // -----

    // public function testFetchRecordSets()
    // {
    //     $expect = [
    //         1 => [
    //             0 => [
    //                 'id' => '1',
    //                 'name' => 'Anna',
    //                 'building' => '1',
    //                 'floor' => '1',
    //             ],
    //             1 => [
    //                 'id' => '4',
    //                 'name' => 'Donna',
    //                 'building' => '1',
    //                 'floor' => '1',
    //             ],
    //         ],
    //         2 => [
    //             0 => [
    //                 'id' => '2',
    //                 'name' => 'Betty',
    //                 'building' => '1',
    //                 'floor' => '2',
    //             ],
    //             1 => [
    //                 'id' => '5',
    //                 'name' => 'Edna',
    //                 'building' => '1',
    //                 'floor' => '2',
    //             ],
    //         ],
    //         3 => [
    //             0 => [
    //                 'id' => '3',
    //                 'name' => 'Clara',
    //                 'building' => '1',
    //                 'floor' => '3',
    //             ],
    //             1 => [
    //                 'id' => '6',
    //                 'name' => 'Fiona',
    //                 'building' => '1',
    //                 'floor' => '3',
    //             ],
    //         ],
    //     ];

    //     $actualRecordSets = $this->mapper->fetchRecordSets([1, 2, 3, 4, 5, 6], 'floor');
    //     $this->assertTrue(is_array($actualRecordSets));
    //     $this->assertCount(3, $actualRecordSets);
    //     foreach ($actualRecordSets as $floor => $actualRecordSet) {
    //         $this->assertInstanceOf(RecordSet::CLASS, $actualRecordSet);
    //         $this->assertCount(2, $actualRecordSet);
    //         $this->assertSame($expect[$floor][0], $actualRecordSet[0]->getRow()->getArrayCopy());
    //         $this->assertSame($expect[$floor][1], $actualRecordSet[1]->getRow()->getArrayCopy());
    //     }
    // }

    // public function testFetchRecordSetsBy()
    // {
    //     $expect = [
    //         1 => [
    //             0 => [
    //                 'id' => '1',
    //                 'name' => 'Anna',
    //                 'building' => '1',
    //                 'floor' => '1',
    //             ],
    //             1 => [
    //                 'id' => '4',
    //                 'name' => 'Donna',
    //                 'building' => '1',
    //                 'floor' => '1',
    //             ],
    //         ],
    //         2 => [
    //             0 => [
    //                 'id' => '2',
    //                 'name' => 'Betty',
    //                 'building' => '1',
    //                 'floor' => '2',
    //             ],
    //             1 => [
    //                 'id' => '5',
    //                 'name' => 'Edna',
    //                 'building' => '1',
    //                 'floor' => '2',
    //             ],
    //         ],
    //         3 => [
    //             0 => [
    //                 'id' => '3',
    //                 'name' => 'Clara',
    //                 'building' => '1',
    //                 'floor' => '3',
    //             ],
    //             1 => [
    //                 'id' => '6',
    //                 'name' => 'Fiona',
    //                 'building' => '1',
    //                 'floor' => '3',
    //             ],
    //         ],
    //     ];

    //     $actualRecordSets = $this->mapper->fetchRecordSetsBy(['id' => [1, 2, 3, 4, 5, 6]], 'floor');
    //     $this->assertTrue(is_array($actualRecordSets));
    //     $this->assertCount(3, $actualRecordSets);
    //     foreach ($actualRecordSets as $floor => $actualRecordSet) {
    //         $this->assertInstanceOf(RecordSet::CLASS, $actualRecordSet);
    //         $this->assertCount(2, $actualRecordSet);
    //         $this->assertSame($expect[$floor][0], $actualRecordSet[0]->getRow()->getArrayCopy());
    //         $this->assertSame($expect[$floor][1], $actualRecordSet[1]->getRow()->getArrayCopy());
    //     }
    // }

    // public function testFetchRecordSetsBySelect()
    // {
    //     $expect = [
    //         1 => [
    //             0 => [
    //                 'id' => '1',
    //                 'name' => 'Anna',
    //                 'building' => '1',
    //                 'floor' => '1',
    //             ],
    //             1 => [
    //                 'id' => '4',
    //                 'name' => 'Donna',
    //                 'building' => '1',
    //                 'floor' => '1',
    //             ],
    //         ],
    //         2 => [
    //             0 => [
    //                 'id' => '2',
    //                 'name' => 'Betty',
    //                 'building' => '1',
    //                 'floor' => '2',
    //             ],
    //             1 => [
    //                 'id' => '5',
    //                 'name' => 'Edna',
    //                 'building' => '1',
    //                 'floor' => '2',
    //             ],
    //         ],
    //         3 => [
    //             0 => [
    //                 'id' => '3',
    //                 'name' => 'Clara',
    //                 'building' => '1',
    //                 'floor' => '3',
    //             ],
    //             1 => [
    //                 'id' => '6',
    //                 'name' => 'Fiona',
    //                 'building' => '1',
    //                 'floor' => '3',
    //             ],
    //         ],
    //     ];

    //     $select = $this->mapper->select(['id' => [1, 2, 3, 4, 5, 6]]);
    //     $actualRecordSets = $this->mapper->fetchRecordSetsBySelect($select, 'floor');
    //     $this->assertTrue(is_array($actualRecordSets));
    //     $this->assertCount(3, $actualRecordSets);
    //     foreach ($actualRecordSets as $floor => $actualRecordSet) {
    //         $this->assertInstanceOf(RecordSet::CLASS, $actualRecordSet);
    //         $this->assertCount(2, $actualRecordSet);
    //         $this->assertSame($expect[$floor][0], $actualRecordSet[0]->getRow()->getArrayCopy());
    //         $this->assertSame($expect[$floor][1], $actualRecordSet[1]->getRow()->getArrayCopy());
    //     }
    // }

