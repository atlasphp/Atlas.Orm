<?php
namespace Atlas\Orm\DataSource\Page;

use Atlas\Orm\Mapper\AbstractMapper;
use Atlas\Orm\DataSource\Comment\CommentMapper;

/**
 * @inheritdoc
 */
class PageMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->oneToMany('comments', CommentMapper::CLASS)
            ->on(['page_id' => 'related_id'])
            ->where('related_type = ?', 'page');
    }
}
