<?php namespace Csatar\Csatar\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Csatar\Csatar\Models\District;
use Csatar\Csatar\Models\Scout;
use Csatar\Csatar\Models\Team;
use Csatar\Forms\Components\BasicForm;
use Input;
use Lang;
use Redirect;
use Response;
use Renatio\DynamicPDF\Classes\PDF;
use Session;

class TeamReport extends ComponentBase
{
    public $id, $teamId, $action, $year, $teamReport, $team, $scouts, $teamFee, $totalAmount, $currency, $status, $basicForm, $redirectFromWaitingForApproval, $errors, $permissions, $confirmDeleteMessage, $confirmRefreshMessage, $legalRelationshipsInAssociation;


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

    public function onRender($isRefresh = false)
    {
        $this->confirmDeleteMessage = Lang::get('backend::lang.form.confirm_delete');
        $this->confirmRefreshMessage = Lang::get('csatar.csatar::lang.plugin.component.teamReport.confirmRefreshMessage');
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

            $this->setVariables();
            if (!isset($this->team)) {
                return Redirect::to('404')->with('message', Lang::get('csatar.csatar::lang.plugin.component.teamReport.validationExceptions.teamCannotBeFound'));
            }

            // retrieve the scouts and calculate fees
            $association = $this->team->district->association;
            $this->teamFee = $association->team_fee;
            $this->totalAmount = $this->teamFee;
            $this->currency = $association->currency->code;
            $this->getScouts($this->teamId);
            $this->basicForm->specialValidationExceptions = $this->errors ?? [];
            unset($this->basicForm->record->belongsToMany['ageGroups']);
        }
        else {
            // edit and view modes - retrieve the team report
            $this->teamReport = \Csatar\Csatar\Models\TeamReport::find($this->id);
            if(isset(Auth::user()->scout)) {
                $this->permissions = Auth::user()->scout->getRightsForModel($this->teamReport);
            }
            if (!isset($this->teamReport)) {
                return Redirect::to('404')->with('message', \Lang::get('csatar.csatar::lang.plugin.component.teamReport.validationExceptions.teamReportCannotBeFound'));
            }
            if ($this->action == 'modositas' and isset($this->teamReport->submitted_at)) {
                return Redirect::to('/csapatjelentes/' . $this->id);
            }
            $this->teamId = $this->teamReport->team_id;
            $this->teamFee = $this->teamReport->team_fee;
            $this->totalAmount = $this->teamReport->total_amount;

            $this->setVariables();
            if (!isset($this->team)) {
                return Redirect::to('404')->with('message', \Lang::get('csatar.csatar::lang.plugin.component.teamReport.validationExceptions.teamCannotBeFound'));
            }
            $this->currency = $this->team->district->association->currency->code;

            // retrieve the scouts and calculate fees
            if (!$this->teamReport->submitted_at && !$this->teamReport->approved_at && $isRefresh) {
                $this->getScouts($this->teamId);
                $this->teamReport->updateScoutsList = true;
                $this->teamReport->save();
                \Flash::success(Lang::get('csatar.csatar::lang.plugin.component.teamReport.successMessages.teamReportRefreshed'));
            } else {
                $this->scouts = $this->teamReport->scouts->lists('pivot');
            }
        }

        // set the team report status
        $this->status = isset($this->teamReport) ? $this->teamReport->getStatus() : Lang::get('csatar.csatar::lang.plugin.admin.teamReport.statuses.notCreated');

        // call the basicForm's onRun method
        array_multisort(array_column($this->scouts, 'name'), SORT_ASC, $this->scouts);
        $this->basicForm->additionalData = $this->renderPartial('@additionalData.htm');
        $this->basicForm->onRun();
    }

    private function setVariables() {
        $this->team = Team::where('id', $this->teamId)->with(['district', 'district.association', 'district.association.currency', 'district.association.legal_relationships'])->first();
        $this->legalRelationshipsInAssociation = $this->team->district->association->legal_relationships;
    }

    public function onSubmit()
    {
        $this->id = Input::get('id');
        $this->teamReport = \Csatar\Csatar\Models\TeamReport::find($this->id);
        $this->teamReport->submitted_at = (new \DateTime())->format('Y-m-d');
        $this->teamReport->save();
        return Redirect::to('/csapatjelentes/' . $this->id);
    }

    public function onDelete()
    {
        $teamReport = \Csatar\Csatar\Models\TeamReport::find(Input::get('id'));
        $teamReport->delete();
        \Flash::success(Lang::get('csatar.csatar::lang.plugin.component.teamReport.successMessages.teamReportDeleted'));
        return Redirect::to('/csapatjelentesek/' . $teamReport->team_id ?? '');
    }

    public function onApprove()
    {
        $this->id = Input::get('id');
        $this->teamReport = \Csatar\Csatar\Models\TeamReport::find($this->id);
        if(isset(Auth::user()->scout)) {
            $this->permissions = Auth::user()->scout->getRightsForModel($this->teamReport, true);
        }
        if ($this->permissions['approved_at']['update'] < 1) {
            \Flash::error(e(trans('csatar.csatar::lang.plugin.admin.teamReport.validationExceptions.noPermissionToApprove')));
            return;
        }
        $this->teamReport->approved_at = (new \DateTime())->format('Y-m-d');
        $this->teamReport->save();
        if (Input::get('redirectFromWaitingForApproval') == 'true') {
            return Redirect::to('/csapatjelentesek/elfogadasravaro');
        }
        return Redirect::to('/csapatjelentes/' . $this->id);
    }

    public function onRefresh(){
        $this->onRender(true);
    }

    public function onDownloadPdf(){

        if (!$id = Input::get('id')) {
            return;
        }

        $fileName = $this->generatePdf($id);

        return Redirect::to("/csapatjelentes-letoltes/$fileName");
    }

    public function generatePdf(int $teamReportId) {
        $templateCode = 'csatar.csatar::pdf.teamreporttemplate'; // unique code of the template

        $teamReport = \Csatar\Csatar\Models\TeamReport::find($teamReportId);

        $data = [
            'css' => \File::get(plugins_path('csatar/csatar/assets/teamReportPdf.css')),
            'teamReport' => $teamReport
        ];

        $fileName = $teamReport->team->id . '-teamreport.pdf';
        PDF::loadTemplate($templateCode, $data)
            ->setOption(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true])
            ->save(temp_path($fileName));

        return $fileName;
    }

    public function getScouts($teamId): void
    {
        $scouts = Scout::where('team_id', $teamId)->where('is_active', true)->get();
        $this->totalAmount = $this->teamFee;

        foreach ($scouts as $scout) {
            $legalRelationShip = $this->team->district->association->legal_relationships->where('id', $scout->legal_relationship_id)->first();
            if(!empty($legalRelationShip)) {
                $membership_fee = $this->team->district->association->legal_relationships->where('id', $scout->legal_relationship_id)->first()->pivot->membership_fee;
            } else {
                $membership_fee = 0;
            }

            $this->scouts[]    = [
                'name'                     => $scout->family_name . ' ' . $scout->given_name,
                'legal_relationship'       => $scout->legal_relationship,
                'legal_relationship_id'    => $scout->legal_relationship->id,
                'leadership_qualification' => $scout->leadership_qualifications->sortByDesc(function ($item, $key) {
                    return $item['pivot']['date'];
                })->values()->first(),
                'ecset_code'               => $scout->ecset_code,
                'membership_fee'           => $membership_fee,
            ];
            $this->totalAmount += $membership_fee;
            if (empty($scout->legal_relationship)) {
                $this->errors[] = Lang::get('csatar.csatar::lang.plugin.component.teamReport.validationExceptions.missingLegalRelationship', [ 'name' => $scout->name ]);
            }
        }
    }
}
