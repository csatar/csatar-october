<?php namespace Csatar\Csatar\Components;

use Lang;
use Cms\Classes\ComponentBase;
use Csatar\Csatar\Models\District;
use Csatar\Csatar\Models\Team;

class TeamReport extends ComponentBase
{
    public $associationId, $teamNumber, $team;

    public function componentDetails()
    {
        return [
            'name' => Lang::get('csatar.csatar::lang.plugin.component.teamReport.name'),
            'description' => Lang::get('csatar.csatar::lang.plugin.component.teamReport.description'),
        ];
    }

    public function onRender()
    {
        // retrieve the parameters
        $this->associationId = $this->property('associationId');
        $this->teamNumber = $this->property('teamNumber');

        // get all district ids, which belong to the association
        $districts_ids = District::where('association_id', $this->associationId)->map(function ($district) {
            return $district['id'];
        });

        // retrieve the team
        $this->team = Team::where('team_number', $this->teamNumber)->whereIn('district_id', $districts_ids)->get();

        // if the team cannot be found, then display an error message
    }
}
