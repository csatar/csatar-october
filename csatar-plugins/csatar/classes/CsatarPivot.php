<?php

namespace Csatar\Csatar\Classes;

use October\Rain\Database\Pivot;

class CsatarPivot extends Pivot
{

    public function getParentClass()
    {
        return get_class($this->parent);
    }

    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    public function getOtherKey()
    {
        return $this->otherKey;
    }

    public function getParent($id = null) {
        return $this->parent->id ? $this->parent : $this->getParentById($id);
    }

    public function getParentById($id) {
        return $this->getParentClass()::find($id);
    }

}
