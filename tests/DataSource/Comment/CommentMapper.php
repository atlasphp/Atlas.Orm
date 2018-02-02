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
        $this->manyToOneReference('commentable', 'related_type')
            ->relate('page', PageMapper::CLASS, ['related_id' => 'page_id'])
            ->relate('post', PostMapper::CLASS, ['related_id' => 'post_id'])
            ->relate('video', VideoMapper::CLASS, ['related_id' => 'video_id']);
    }
}
