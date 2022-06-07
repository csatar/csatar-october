<?php namespace Csatar\Csatar\Components;

use Lang;
use Cms\Classes\ComponentBase;
use Csatar\Csatar\Models\District;
use Csatar\Csatar\Models\Team;
use Csatar\Forms\Components\BasicForm;

class TeamReport extends ComponentBase
{
    public $teamId, $action, $year, $teamReport, $team, $basicForm;

    public function init()
    {
        $this->basicForm = $this->addComponent(BasicForm::class, 'basicForm', [
            'formSlug' => 'csapatjelentes',
            'recordKeyParam' => 'id',
            'recordActionParam' => 'action',
            'createRecordKeyword' => 'letrehozas',
            'actionUpdateKeyword' => 'modositas',
        ]);
    }

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
        $this->teamId = $this->param('id');
        $this->action = $this->param('action');

        // if we are in creation mode, then retrieve the team ID from session
        if ($this->teamId == $this->basicForm->createRecordKeyword) {
            $this->action = $this->teamId;
            $this->teamId = \Session::get('id');
        }

        // try to retrieve the team report
        $this->year = date('n') == 1 ? date('Y') - 1 : date('Y');
        $this->teamReport = \Csatar\Csatar\Models\TeamReport::where('team_id', $this->teamId)->where('year', $this->year)->first();

        // depending on the team report's status, define different behavior
        if (isset($this->teamReport)) {
            
        }

        // retrieve the team
        $this->team = Team::find($this->teamId);

        // if the team cannot be found, then display an error message
        if (!isset($this->team)) {
        //    return \Redirect::to('404')->with('message', \Lang::get('csatar.csatar::lang.plugin.component.teamReport.validationExceptions.teamCannotBeFound'));
        }
    }

    public function onRun()
    {
        $this->basicForm->additionalData = '<p>Proba</p>';
        $this->basicForm->onRun();
    }
}
