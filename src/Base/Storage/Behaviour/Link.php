<?php

namespace Base\Storage\Behaviour;

use Base\Storage\Component\Model;

class Link
{
    public $model;
    public $list = array();

    function hasVirtualPk()
    {
        return false;
    }

    function getForeignModel(Model $model)
    {
        foreach($this->list as $m) {
            if($m != $model) {
                return $m;
            }
        }
    }
}