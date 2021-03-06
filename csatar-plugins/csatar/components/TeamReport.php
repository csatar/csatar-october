<?php namespace Csatar\Csatar\Components;

use Input;
use Lang;
use Redirect;
use Cms\Classes\ComponentBase;
use Csatar\Csatar\Models\District;
use Csatar\Csatar\Models\Scout;
use Csatar\Csatar\Models\Team;
use Csatar\Forms\Components\BasicForm;

class TeamReport extends ComponentBase
{
    public $id, $teamId, $action, $year, $teamReport, $team, $scouts, $teamFee, $totalAmount, $currency, $status, $basicForm, $redirectFromWaitingForApproval;

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
        $this->year = date('n') == 1 ? date('Y') - 1 : date('Y');

        // retrieve the parameters
        $this->id = $this->param('id');
        $this->action = $this->param('action');
        $this->redirectFromWaitingForApproval = Input::get('redirectFromWaitingForApproval') ?? 'false';

        // actions and redirections depending on the mode
        if ($this->id == $this->basicForm->createRecordKeyword) {
            // create mode
            $this->action = $this->id;
            $this->teamId = Input::get('team');

            // retrieve the team report
            $this->teamReport = \Csatar\Csatar\Models\TeamReport::where('team_id', $this->teamId)->where('year', $this->year)->first();

            // depending on the team report's status, redirect to different modes
            if (isset($this->teamReport)) {
                if (isset($this->teamReport->submitted_at)) {
                    // view mode
                    return Redirect::to('/csapatjelentes/' . $this->teamReport->id);
                }
                else {
                    // edit mode
                    return Redirect::to('/csapatjelentes/' . $this->teamReport->id . '/modositas');
                }
            }

            // retrieve the team
            $this->team = Team::find($this->teamId);
            if (!isset($this->team)) {
                return Redirect::to('404')->with('message', Lang::get('csatar.csatar::lang.plugin.component.teamReport.validationExceptions.teamCannotBeFound'));
            }

            // retrieve the scouts and calculate fees
            $association = $this->team->district->association;
            $this->teamFee = $association->team_fee;
            $this->totalAmount = $this->teamFee;
            $this->currency = $association->currency->code;
            $this->scouts = [];
            $scouts = Scout::where('team_id', $this->teamId)->where('is_active', true)->get();
            foreach ($scouts as $scout) {
                $membership_fee = $this->team->district->association->legal_relationships->where('id', $scout->legal_relationship_id)->first()->pivot->membership_fee;

                array_push($this->scouts, [
                    'name' => $scout->family_name . ' ' . $scout->given_name,
                    'legal_relationship' => $scout->legal_relationship,
                    'leadership_qualification' => $scout->leadership_qualifications->sortByDesc(function ($item, $key) {
                        return $item['pivot']['date'];
                    })->values()->first(),
                    'ecset_code' => $scout->ecset_code,
                    'membership_fee' => $membership_fee,
                ]);
                $this->totalAmount += $membership_fee;
            }
        }
        else {
            // edit and view modes - retrieve the team report
            $this->teamReport = \Csatar\Csatar\Models\TeamReport::find($this->id);
            if (!isset($this->teamReport)) {
                return Redirect::to('404')->with('message', \Lang::get('csatar.csatar::lang.plugin.component.teamReport.validationExceptions.teamReportCannotBeFound'));
            }
            if ($this->action == 'modositas' and isset($this->teamReport->submitted_at)) {
                return Redirect::to('/csapatjelentes/' . $this->id);
            }
            $this->teamId = $this->teamReport->team_id;
            $this->teamFee = $this->teamReport->team_fee;
            $this->totalAmount = $this->teamReport->total_amount;

            // retrieve the team
            $this->team = Team::find($this->teamId);
            if (!isset($this->team)) {
                return Redirect::to('404')->with('message', \Lang::get('csatar.csatar::lang.plugin.component.teamReport.validationExceptions.teamCannotBeFound'));
            }
            $this->currency = $this->team->district->association->currency->code;

            // retrieve the scouts and calculate fees
            $this->scouts = $this->teamReport->scouts->lists('pivot');
        }

        // set the team report status
        $this->status = isset($this->teamReport) ? $this->teamReport->getStatus() : Lang::get('csatar.csatar::lang.plugin.admin.teamReport.statuses.notCreated');

        // call the basicForm's onRun method
        array_multisort(array_column($this->scouts, 'name'), SORT_ASC, $this->scouts);
        $this->basicForm->additionalData = $this->renderPartial('@additionalData.htm');
        $this->basicForm->onRun();
    }

    public function onSubmit()
    {
        $this->id = Input::get('id');
        $this->teamReport = \Csatar\Csatar\Models\TeamReport::find($this->id);
        $this->teamReport->submitted_at = (new \DateTime())->format('Y-m-d');
        $this->teamReport->save();
        return Redirect::to('/csapatjelentes/' . $this->id);
    }

    public function onApprove()
    {
        $this->id = Input::get('id');
        $this->teamReport = \Csatar\Csatar\Models\TeamReport::find($this->id);
        $this->teamReport->approved_at = (new \DateTime())->format('Y-m-d');
        $this->teamReport->save();
        if (Input::get('redirectFromWaitingForApproval') == 'true') {
            return Redirect::to('/csapatjelentesek/elfogadasravaro');
        }
        return Redirect::to('/csapatjelentes/' . $this->id);
    }
}
