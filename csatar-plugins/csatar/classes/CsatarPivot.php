<?php

namespace Csatar\Csatar\Classes;

use October\Rain\Database\Pivot;

class CsatarPivot extends Pivot
{

    public function getParentClass()
    {
        if (!empty($this->pivotParent) && is_object($this->pivotParent)) {
            return get_class($this->pivotParent);
        }

        return null;
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
        return $this->parent ? $this->parent : $this->getParentById($id);
    }

    public function getParentById($id) {
        return $this->getParentClass() ? $this->getParentClass()::find($id) : null;
    }

    public function getId() {
        return $this->id ?? null;
    }

}
