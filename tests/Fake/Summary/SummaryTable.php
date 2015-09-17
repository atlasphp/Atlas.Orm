<?php
namespace Atlas\Fake\Summary;

use Atlas\Table\Table;

class SummaryTable extends Table
{
    protected $table = 'summaries';
    protected $primary = 'thread_id';
    protected $autoinc = 'false';
}
