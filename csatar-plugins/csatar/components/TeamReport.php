<?php namespace Csatar\Csatar\Components;

use Lang;
use Cms\Classes\ComponentBase;

class TeamReport extends ComponentBase
{
    public $associationId, $teamNumber;

    public function componentDetails()
    {
        return [
            'name' => Lang::get('csatar.csatar::lang.plugin.component.teamReport.name'),
            'description' => Lang::get('csatar.csatar::lang.plugin.component.teamReport.description'),
        ];
    }

    public function onRender()
    {
        $this->associationId = $this->property('associationId');
        $this->teamNumber = $this->property('teamNumber');
    }
}
