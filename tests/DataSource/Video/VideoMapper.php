<?php
namespace Atlas\Orm\DataSource\Video;

use Atlas\Orm\Mapper\AbstractMapper;
use Atlas\Orm\DataSource\Comment\CommentMapper;

/**
 * @inheritdoc
 */
class VideoMapper extends AbstractMapper
{
    /**
     * @inheritdoc
     */
    protected function setRelated()
    {
        $this->oneToMany('comments', CommentMapper::CLASS)
            ->on(['video_id' => 'related_id'])
            ->where('related_type = ?', 'video');
    }
}
