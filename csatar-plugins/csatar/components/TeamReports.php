<?php
namespace Csatar\Csatar\Components;

use Auth;
use Carbon\Carbon;
use DateTime;
use Lang;
use Redirect;
use Cms\Classes\ComponentBase;
use Csatar\Csatar\Models\Scout;
use Csatar\Csatar\Models\Team;
use Csatar\Csatar\Models\TeamReport;

class TeamReports extends ComponentBase
{
    public $id;
    public $waitingForApprovalMode;
    public $team;
    public $teamReports;
    public $legalRelationships;
    public $teamReportData;
    public $showTeamReportCreateButton;
    public $permissions;
    public $permissionForCreateButton;
    public $listingAll;

    public function componentDetails()
    {
        return [
            'name' => Lang::get('csatar.csatar::lang.plugin.component.teamReports.name'),
            'description' => Lang::get('csatar.csatar::lang.plugin.component.teamReports.description'),
        ];
    }

    public function onRun() {
        if (!Auth::user()->scout) {
            \App::abort(403, 'Access denied!');
        }

        $newTeamReport                = new TeamReport();
        $newTeamReport->team_id       = $this->param('id') && is_numeric($this->param('id')) ? $this->param('id') : Auth::user()->scout->team_id;
        $generalTeamReportPermissions = Auth::user()->scout->getRightsForModel($newTeamReport);

        if ($generalTeamReportPermissions['MODEL_GENERAL']['read'] < 1) {
            \App::abort(403, 'Access denied!');
        }

        $this->permissionForCreateButton = $this->param('id') ? $generalTeamReportPermissions['MODEL_GENERAL']['create'] : false;
    }

    public function onRender()
    {
        // retrieve the parameters
        $this->id = $this->param('id');

        // if the list of team reports that are waiting for approval is requested
        if ($this->id == 'elfogadasravaro') {
            $this->listWaitingForApproval();
        } elseif ($this->id == null) {
            $this->listAll();
        } else {
            return $this->listTeamSpecific();
        }
    }

    public function listWaitingForApproval(): void
    {
        $this->waitingForApprovalMode = true;

        // retrieve the team reports
        $this->teamReports = TeamReport::where('submitted_at', '<>', null)->where('approved_at', null)->orderBy('year', 'desc')->get();

        // create the array with the data to display in the table
        $this->teamReportData = [];
        foreach ($this->teamReports as $teamReport) {
            if (Auth::user()->scout->getRightsForModel($teamReport)['MODEL_GENERAL']['read']) {
                $this->teamReportData[] = [
                    'id'            => $teamReport->id,
                    'team_name'     => $teamReport->team->extendedName,
                    'year'          => $teamReport->year,
                    'members_count' => count($teamReport->scouts),
                    'total_amount'  => $teamReport->total_amount . ' ' . $teamReport->currency->code,
                    'submitted_at'  => (new DateTime($teamReport->submitted_at))->format('Y-m-d'),
                ];
            }

            $this->permissions[$teamReport->id] = Auth::user()->scout->getRightsForModel($teamReport)['MODEL_GENERAL'] ?? null;
        }
    }

    public function listAll(): void
    {
        $this->listingAll  = true;
        $associationId     = Auth::user()->scout->getAssociation()->id;
        $teamIds           = Team::activeInAssociation($associationId)->get()->pluck('id')->toArray();
        $this->teamReports = TeamReport::whereIn('team_id', $teamIds)->orderBy('year', 'desc')->get();

        // create the array with the data to display in the table
        $this->teamReportData = [];
        foreach ($this->teamReports as $teamReport) {
            if (Auth::user()->scout->getRightsForModel($teamReport)['MODEL_GENERAL']['read']) {
                $this->teamReportData[] = [
                    'id'            => $teamReport->id,
                    'team_number'   => $teamReport->team->team_number,
                    'team_name'     => $teamReport->team->extendedName,
                    'year'          => $teamReport->year,
                    'members_count' => count($teamReport->scouts),
                    'total_amount'  => $teamReport->total_amount . ' ' . $teamReport->currency->code,
                    'submitted_at'  => (new DateTime($teamReport->submitted_at))->format('Y-m-d'),
                    'status'        => $teamReport->getStatus(),
                    'link'          => isset($teamReport->submitted_at) ? '/csapatjelentes/' . $teamReport->id : '/csapatjelentes/' . $teamReport->id . '/modositas',
                    'link_text'     => isset($teamReport->submitted_at) ?
                        Lang::get('csatar.csatar::lang.plugin.component.teamReports.view') :
                        Lang::get('csatar.csatar::lang.plugin.component.teamReports.edit'),
                ];
            }

            $this->permissions[$teamReport->id] = Auth::user()->scout->getRightsForModel($teamReport)['MODEL_GENERAL'] ?? null;
        }

        $this->teamReportData = collect($this->teamReportData);
        $this->teamReportData = $this->teamReportData->sortBy(function ($teamReport) {
            return -$teamReport['year'] * 1000000 + $teamReport['team_number'];
        });
    }

    public function listTeamSpecific()
    {
        $this->waitingForApprovalMode = false;

        // retrieve the team
        $this->team = Team::find($this->id);
        if (!isset($this->team)) {
            return Redirect::to('404')->with('message', Lang::get('csatar.csatar::lang.plugin.component.teamReport.validationExceptions.teamCannotBeFound'));
        }

        $this->permissions = Auth::user()->scout->getRightsForModel($this->team);

        // retrieve the team reports
        $this->teamReports = TeamReport::where('team_id', $this->id)->orderBy('year', 'desc')->get();

        // determine whether the Team Report Create button should be shown
        $month         = date('n');
        $year          = $month <= 5 ? date('Y') - 1 : date('Y');
        $hasPermission = isset($this->permissions['teamReports']['create']) && $this->permissions['teamReports']['create'] > 0;
        $isInTeamReportSubmitPeriod = false;
        if ($association = $this->team->getAssociation()) {
            $isInTeamReportSubmitPeriod = Carbon::now()->gte(new Carbon($association->team_report_submit_start_date))
                && Carbon::now()->lte((new Carbon($association->team_report_submit_end_date))->endOfDay());
        }

        $this->showTeamReportCreateButton = count($this->teamReports->where('year', $year)) == 0 && $hasPermission && $isInTeamReportSubmitPeriod;

        // create the list of the defined legal relationships for the association
        $this->legalRelationships = $this->team->district->association->legal_relationships;

        // create the array with the data to display in the table
        $this->teamReportData = [];
        foreach ($this->teamReports as $teamReport) {
            // retrieve the list of TeamReport - Scout pivot data
            $scouts = Scout::where('team_id', $teamReport->team_id)->withTrashed()->get()->map(function ($scout) {
                return $scout->team_reports->lists('pivot');
            });

            // count the scouts
            $scoutsCount = 0;
            $scoutsDataCountPerLegalRelationship = [];
            foreach ($scouts as $scout) {
                foreach ($scout as $item) {
                    if ($item->team_report_id == $teamReport->id) {
                        $scoutsCount++;
                        $scoutsDataCountPerLegalRelationship[$item->legal_relationship_id] =
                            array_key_exists($item->legal_relationship_id, $scoutsDataCountPerLegalRelationship) ?
                                $scoutsDataCountPerLegalRelationship[$item->legal_relationship_id] + 1 : 1;
                    }
                }
            }

            // construct the array containing the data, which will be displayed in the table
            $data = [
                'id'            => $teamReport->id,
                'year'          => $teamReport->year,
                'members_count' => $scoutsCount,
                'total_amount'  => $teamReport->total_amount . ' ' . $teamReport->currency->code,
                'status'        => $teamReport->getStatus(),
                'link'          => isset($teamReport->submitted_at) ? '/csapatjelentes/' . $teamReport->id : '/csapatjelentes/' . $teamReport->id . '/modositas',
                'link_text'     => isset($teamReport->submitted_at) ?
                    Lang::get('csatar.csatar::lang.plugin.component.teamReports.view') :
                    Lang::get('csatar.csatar::lang.plugin.component.teamReports.edit'),
            ];
            foreach ($this->legalRelationships as $legalRelationship) {
                $data[$legalRelationship->id] = $scoutsDataCountPerLegalRelationship[$legalRelationship->id] ?? 0;
            }

            array_push($this->teamReportData, $data);
            $this->permissions[$teamReport->id] = Auth::user()->scout->getRightsForModel($teamReport)['MODEL_GENERAL'] ?? null;
        }
    }

}
