<?php namespace Csatar\Csatar\Components;

use DateTime;
use Lang;
use Redirect;
use Cms\Classes\ComponentBase;
use Csatar\Csatar\Models\Scout;
use Csatar\Csatar\Models\Team;
use Csatar\Csatar\Models\TeamReport;

class TeamReports extends ComponentBase
{
    public $id, $waitingForApprovalMode, $team, $teamReports, $legalRelationships, $teamReportData, $showTeamReportCreateButton;

    public function componentDetails()
    {
        return [
            'name' => Lang::get('csatar.csatar::lang.plugin.component.teamReports.name'),
            'description' => Lang::get('csatar.csatar::lang.plugin.component.teamReports.description'),
        ];
    }

    public function onRender()
    {
        // retrieve the parameters
        $this->id = $this->param('id');

        // if the list of team reports that are waiting for approval is requested
        if ($this->id == 'elfogadasravaro') {
            $this->waitingForApprovalMode = true;

            // retrieve the team reports
            $this->teamReports = TeamReport::where('submitted_at', '<>', null)->where('approved_at', null)->orderBy('year', 'desc')->get();

            // create the array with the data to display in the table
            $this->teamReportData = [];
            foreach ($this->teamReports as $teamReport) {
                array_push($this->teamReportData, [
                    'id' => $teamReport->id,
                    'team_name' => $teamReport->team->extendedName,
                    'year' => $teamReport->year,
                    'members_count' => count($teamReport->scouts),
                    'total_amount' => $teamReport->total_amount . ' ' . $teamReport->currency->code,
                    'submitted_at' => (new DateTime($teamReport->submitted_at))->format('Y-m-d'),
                ]);
            }
        }
        else {
            $this->waitingForApprovalMode = false;

            // retrieve the team
            $this->team = Team::find($this->id);
            if (!isset($this->team)) {
                return Redirect::to('404')->with('message', Lang::get('csatar.csatar::lang.plugin.component.teamReport.validationExceptions.teamCannotBeFound'));
            }

            // retrieve the team reports
            $this->teamReports = TeamReport::where('team_id', $this->id)->orderBy('year', 'desc')->get();
            
            // determine whether the Team Report Create button should be shown
            $month = date('n');
            $year = $month == 1 ? date('Y') - 1 : date('Y');
            $this->showTeamReportCreateButton = count($this->teamReports->where('year', $year)) == 0 /*&& ($month == 1 || $month == 12 || $month == 6)*/;

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
                            $scoutsDataCountPerLegalRelationship[$item->legal_relationship_id] = array_key_exists($item->legal_relationship_id, $scoutsDataCountPerLegalRelationship) ? $scoutsDataCountPerLegalRelationship[$item->legal_relationship_id] + 1 : 1;
                        }
                    }
                }
                
                // construct the array containing the data, which will be displayed in the table
                $data = [
                    'year' => $teamReport->year,
                    'members_count' => $scoutsCount,
                    'total_amount' => $teamReport->total_amount . ' ' . $teamReport->currency->code,
                    'status' => $teamReport->getStatus(),
                    'link' => isset($teamReport->submitted_at) ? '/csapatjelentes/' . $teamReport->id : '/csapatjelentes/' . $teamReport->id . '/modositas',
                    'link_text' => isset($teamReport->submitted_at) ? Lang::get('csatar.csatar::lang.plugin.component.teamReports.view') : Lang::get('csatar.csatar::lang.plugin.component.teamReports.edit'),
                ];
                foreach ($this->legalRelationships as $legalRelationship) {
                    $data[$legalRelationship->id] = $scoutsDataCountPerLegalRelationship[$legalRelationship->id] ?? 0;
                }
                array_push($this->teamReportData, $data);
            }
        }
    }
}
