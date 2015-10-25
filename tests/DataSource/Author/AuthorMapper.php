<?php
namespace Atlas\DataSource\Author;

use Atlas\Mapper\AbstractMapper;

class AuthorMapper extends AbstractMapper
{
    public function __construct(
        AuthorTable $table,
        AuthorRecordFactory $recordFactory,
        AuthorRelations $relations
    ) {
        parent::__construct($table, $recordFactory, $relations);
    }
}
