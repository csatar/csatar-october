<?php
namespace Csatar\Csatar\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Csatar\Csatar\Models\PermissionBasedAccess;

/**
 * Breadcrumb Component
 */
class Breadcrumb extends ComponentBase
{
    public $parentOptions = [
        '\Csatar\Csatar\Models\TeamReport' => '\Csatar\Csatar\Models\Team',
        '\Csatar\Csatar\Models\Scout' => [
            '\Csatar\Csatar\Models\Patrol', // order matters here
            '\Csatar\Csatar\Models\Troop',
            '\Csatar\Csatar\Models\Team',
        ],
        '\Csatar\Csatar\Models\Patrol' => [ // order matters here
            '\Csatar\Csatar\Models\Troop',
            '\Csatar\Csatar\Models\Team',
        ],
        '\Csatar\Csatar\Models\Troop' => '\Csatar\Csatar\Models\Team',
        '\Csatar\Csatar\Models\Team' => '\Csatar\Csatar\Models\District',
        '\Csatar\Csatar\Models\District' => '\Csatar\Csatar\Models\Association'
    ];

    public $modelLinkTitleMap = [
        '\Csatar\Csatar\Models\Association' => 'name_abbreviation',
        '\Csatar\Csatar\Models\District' => 'name',
        '\Csatar\Csatar\Models\Team' => 'team_number',
        '\Csatar\Csatar\Models\Troop' => 'extendedName',
        '\Csatar\Csatar\Models\Patrol' => 'name',
        '\Csatar\Csatar\Models\Scout' => 'name',
    ];

    public $urlList = [];

    public function componentDetails()
    {
        return [
            'name' => 'Breadcrumb Component',
            'description' => 'Csatár Breadcrumb Component'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun(){
        $isFormBuilderPresent = isset($this->controller->vars['basicForm']);
        $isTeamReportPage     = isset($this->controller->vars['teamReports']->team);

        if ($isFormBuilderPresent) {
            $basicForm     = $this->controller->vars['basicForm'];
            $currentRecord = $basicForm->record;

            if (!($currentRecord instanceof PermissionBasedAccess)) {
                return;
            }

            if (!empty($currentRecord)) {
                $currentRecordName = $currentRecord->getModelName();
                $this->getRecordUrl($currentRecord, $currentRecordName, true);
                $this->getAncestorsTree($currentRecord);
            }
        } elseif ($isTeamReportPage) {
            $teamReport    = $this->controller->vars['teamReports'];
            $currentRecord = $teamReport->team;

            if (!empty($currentRecord)) {
                $this->urlList[] = [
                    'linkTitle' => e(trans('csatar.csatar::lang.plugin.component.teamReports.name')),
                    'url'       => '',
                ];

                $currentRecordName = $currentRecord->getModelName();
                $this->getRecordUrl($currentRecord, $currentRecordName);
                $this->getAncestorsTree($currentRecord);
            }
        } else {
            $this->urlList[] = [
                'linkTitle' => $this->page->title ?? '',
                'url'       => '',
            ];

            if (isset(Auth::user()->scout)) {
                $currentRecord = Auth::user()->scout->team;

                if (!empty($currentRecord)) {
                    $currentRecordName = $currentRecord->getModelName();
                    $this->getRecordUrl($currentRecord, $currentRecordName);
                    $this->getAncestorsTree($currentRecord);
                }
            }
        }
    }

    private function getAncestorsTree($currentRecord) {

        $currentRecordName = $currentRecord->getModelName();

        if (isset($this->parentOptions[$currentRecordName])) {
            $parentInfo = $this->parentOptions[$currentRecordName];
            if (is_array($parentInfo)) {
                foreach ($parentInfo as $possibleParentModel) {
                    $parent = $this->getParent($currentRecord, $possibleParentModel);
                }
            } else {
                $parent = $this->getParent($currentRecord, $parentInfo);
            }
        }

        if (!empty($parent)) {
            $this->getAncestorsTree($parent);
        }
    }

    private function getParent($currentRecord, $possibleParentModel) {
        $parentRelationName = array_search($possibleParentModel, $currentRecord->belongsTo);

        if (empty($parentRelationName)) {
            foreach ($currentRecord->belongsTo as $key => $relationArray) {
                if (is_array($relationArray)) {
                    $parentRelationName = array_search($possibleParentModel, $relationArray) !== false ? $key : null;
                    if (!empty($parentRelationName)) {
                        break;
                    }
                }
            }
        }

        $parent = $currentRecord->{$parentRelationName};
        if (!empty($parent) && $parent instanceof PermissionBasedAccess) {
            $this->getRecordUrl($parent, $possibleParentModel);
            return $parent;
        }

        return null;
    }

    private function getRecordUrl (PermissionBasedAccess $model, string $modelName, bool $isLastDescendant = false): void
    {
        $modelNameUserFriendly = $modelName::getOrganizationTypeModelNameUserFriendly('hu');
        $recordName            = isset($this->modelLinkTitleMap[$modelName]) ? $model->{$this->modelLinkTitleMap[$modelName]} : '';
        $modelSlug = str_slug($modelNameUserFriendly);
        $url       = $this->controller->pageUrl($modelSlug, [ 'id' => $model->id ] );
        $this->urlList[] = [
            'linkTitle' => !empty($recordName) ? $recordName : $modelNameUserFriendly,
            'url'       => $isLastDescendant ? '' : $url,
        ];
    }

}
