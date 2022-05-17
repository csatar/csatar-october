<?php namespace Csatar\Csatar\Components;

use Lang;
use Cms\Classes\ComponentBase;

class Structure extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => Lang::get('csatar.csatar::lang.plugin.component.structure.name'),
            'description' => Lang::get('csatar.csatar::lang.plugin.component.structure.description'),
        ];
    }

    public function onRun()
    {
    }
}
