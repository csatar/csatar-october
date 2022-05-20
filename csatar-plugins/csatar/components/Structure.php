<?php namespace Csatar\Csatar\Components;

use Lang;
use Cms\Classes\ComponentBase;
use Csatar\Csatar\Models\Association;

class Structure extends ComponentBase
{
    public $associations;

    public function componentDetails()
    {
        return [
            'name' => Lang::get('csatar.csatar::lang.plugin.component.structure.name'),
            'description' => Lang::get('csatar.csatar::lang.plugin.component.structure.description'),
        ];
    }

    public function onRun()
    {
        $this->associations = Association::all();
    }
}
