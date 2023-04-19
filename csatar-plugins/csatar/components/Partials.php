<?php
namespace Csatar\Csatar\Components;

use Cms\Classes\ComponentBase;
use Lang;

/**
 * Partials Component
 */
class Partials extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name' => Lang::get('csatar.csatar::lang.plugin.component.partial.name'),
            'description' => Lang::get('csatar.csatar::lang.plugin.component.partial.description'),
        ];
    }

}
