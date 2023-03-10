<?php namespace Csatar\Csatar\Controllers;

use Backend;
use BackendMenu;
use Backend\Classes\Controller;
use Carbon\Carbon;
use Csatar\Csatar\Classes\CsvCreator;
use Csatar\Csatar\Classes\Enums\Status;
use Csatar\Csatar\Models\MembershipCardRequest;
use Db;
use Flash;
use Lang;
use Redirect;
use Response;

class MembershipCardRequests extends Controller
{
    public $implement = [
        'Backend\Behaviors\ListController',
    ];

    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Csatar.Csatar', 'main-menu-item-organization-system-data', 'side-menu-item');
    }

    public function listExtendQuery($query, $definition = null)
    {
        $query = $this->extendQuery($query);
    }

    public $requiredPermissions = [
        'csatar.manage.data'
    ];

    public function requests() {

        $this->pageTitle = 'Membership Card Requests';
        $this->bodyClass = 'compact-container';

        $this->prepareVars();
    }

    public function prepareVars() {
        $this->vars['requests'] = $this->getMembersWithoutMemberCard();
        $this->vars['attributesWithLabels'] = $this->getAttributesWithLabels();
    }

    public function getAttributesWithLabels() {
        return [
            'team_number' => Lang::get('csatar.csatar::lang.plugin.admin.team.teamNumber'),
            'name' => Lang::get('csatar.csatar::lang.plugin.admin.general.name'),
            'ecset_code' => Lang::get('csatar.csatar::lang.plugin.admin.general.ecsetCode')
        ];
    }

    /**
     * @return void
     */
    public function getMembersWithoutMemberCard()
    {
        $query = MembershipCardRequest::query();
        $query = $this->extendQuery($query);
        return $query->get();
    }

    public function extendQuery($query)
    {
        $memberLegalRelationshipId = \Csatar\Csatar\Models\LegalRelationship::where('name', 'Tag')->first()->id;

        return $query->select('csatar_csatar_teams.team_number', 'csatar_csatar_team_reports_scouts.name', 'csatar_csatar_team_reports_scouts.ecset_code')
            ->join('csatar_csatar_team_reports', 'csatar_csatar_team_reports.id', '=', 'csatar_csatar_team_reports_scouts.team_report_id')
            ->join('csatar_csatar_teams', 'csatar_csatar_team_reports.team_id', '=', 'csatar_csatar_teams.id')
            ->join('csatar_csatar_scouts', 'csatar_csatar_team_reports_scouts.scout_id', '=', 'csatar_csatar_scouts.id')
            ->whereNotNull('csatar_csatar_team_reports.submitted_at')
            ->whereNull('csatar_csatar_team_reports.deleted_at')
            ->whereNull('csatar_csatar_scouts.deleted_at')
            ->whereNull('csatar_csatar_scouts.inactivated_at')
            ->where('csatar_csatar_team_reports_scouts.legal_relationship_id', $memberLegalRelationshipId)
            ->whereNotIn('csatar_csatar_team_reports_scouts.scout_id', \Db::table('csatar_csatar_membership_cards')->pluck('scout_id'))
            ->orderBy('csatar_csatar_teams.team_number')
            ->orderBy('csatar_csatar_team_reports_scouts.name')
            ->distinct(['csatar_csatar_team_reports_scouts.ecset_code']);
    }

    public function onExportToCsv()
    {
        $fileName = Carbon::today()->toDateString() . '.csv';
        $csvPath  = temp_path() . '/' . $fileName;
        $this->prepareVars();

        if (empty($this->vars['requests'])) {
            Flash::error('No data to export');
            return;
        }

        $data = [
            array_values($this->vars['attributesWithLabels']),
        ];

        foreach ($this->vars['requests'] as $record) {
            $dataRow = [];
            foreach (array_keys($this->vars['attributesWithLabels']) as $attribute) {
                $dataRow[] = $record->{$attribute};
            }

            $data[] = $dataRow;
        }

        CsvCreator::writeCsvFile($csvPath, $data);

        return Redirect::to(Backend::url('csatar/csatar/membershipcardrequests/download'));
    }

    public function download() {
        $fileName = Carbon::today()->toDateString() . '.csv';
        $csvPath = temp_path() . '/' . $fileName;
        $headers = [
            'Content-Type' => 'text/csv',
            'charset' => 'UTF-8',
        ];

        return Response::download($csvPath, $fileName, $headers)->deleteFileAfterSend(true);
    }
}
