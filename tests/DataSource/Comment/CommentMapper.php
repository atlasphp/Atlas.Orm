<?php
namespace Atlas\Orm\DataSource\Comment;

use Atlas\Orm\Mapper\AbstractMapper;
use Atlas\Orm\DataSource\Page\PageMapper;
use Atlas\Orm\DataSource\Post\PostMapper;
use Atlas\Orm\DataSource\Video\VideoMapper;

/**
 * @inheritdoc
 */
class CommentMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->manyToOneVariant('commentable', 'related_type')
            ->variant('page', PageMapper::CLASS, ['related_id' => 'page_id'])
            ->variant('post', PostMapper::CLASS, ['related_id' => 'post_id'])
            ->variant('video', VideoMapper::CLASS, ['related_id' => 'video_id']);
    }
}
