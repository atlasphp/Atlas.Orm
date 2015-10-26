<?php
namespace Atlas\DataSource\Thread;

use Atlas\Table\AbstractRowFactory;

class ThreadRowFactory extends AbstractRowFactory
{
    public function getPrimary()
    {
        return 'thread_id';
    }

    public function getDefault()
    {
        return [
            'thread_id' => null,
            'author_id' => null,
            'subject' => null,
            'body' => null,
        ];
    }
}
