<?php namespace Csatar\Csatar\Components;

use Cms\Classes\ComponentBase;

/**
 * Partials Component
 */
class Partials extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Partials Component',
            'description' => 'Component to share partials between plugins'
        ];
    }
}
