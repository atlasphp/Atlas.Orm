<?php
namespace Atlas\Orm\DataSource\Post;

use Atlas\Orm\Mapper\AbstractMapper;
use Atlas\Orm\DataSource\Comment\CommentMapper;

/**
 * @inheritdoc
 */
class PostMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->oneToMany('comments', CommentMapper::CLASS)
            ->on(['post_id' => 'related_id'])
            ->where('related_type = ?', 'post');
    }
}
