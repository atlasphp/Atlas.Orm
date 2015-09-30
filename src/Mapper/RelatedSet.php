<?php
namespace Atlas\Mapper;

class RelatedSet
{
    protected $relateds = [];

    public function __construct(array $relateds)
    {
        $this->relateds = $relateds;
    }

    public function get($primary)
    {
        return $this->relateds[$primary];
    }
}
