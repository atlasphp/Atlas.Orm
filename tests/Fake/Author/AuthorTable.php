<?php
namespace Atlas\Fake\Author;

use Atlas\Table\Table;

class AuthorTable extends Table
{
    protected $table = 'authors';
    protected $default = [
        'name' => null,
    ];
}
