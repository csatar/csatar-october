<?php namespace Csatar\Csatar\Components;

use Auth;
use Cache;
use Lang;
use Cms\Classes\ComponentBase;
use Csatar\Csatar\Models\Association;
use Csatar\Csatar\Models\District;
use Csatar\Csatar\Models\Team;
use Csatar\Csatar\Models\Troop;
use Csatar\Csatar\Models\Patrol;
use Csatar\Csatar\Models\Scout;

class Structure extends ComponentBase
{
    public $structureArray;
    public $level;
    public $displayHeader = false;
    public $mode;
    public $permissions;
    public $showActiveScouts;

    public function componentDetails()
    {
        return [
            'name' => Lang::get('csatar.csatar::lang.plugin.component.structure.name'),
            'description' => Lang::get('csatar.csatar::lang.plugin.component.structure.description'),
        ];
    }

    public function defineProperties()
    {
        return [
            'level' => [
                'title'             => 'csatar.csatar::lang.plugin.component.structure.properties.level.title',
                'description'       => 'csatar.csatar::lang.plugin.component.structure.properties.level.description',
                'type'              => 'string',
                'default'           => null
            ],
            'model_name' => [
                'title'             => 'csatar.csatar::lang.plugin.component.structure.properties.model_name.title',
                'description'       => 'csatar.csatar::lang.plugin.component.structure.properties.model_name.description',
                'type'              => 'string',
                'default'           => null
            ],
            'model_id' => [
                'title'             => 'csatar.csatar::lang.plugin.component.structure.properties.model_id.title',
                'description'       => 'csatar.csatar::lang.plugin.component.structure.properties.model_id.description',
                'type'              => 'string',
                'default'           => null
            ],
            'mode' => [
                'title'             => 'csatar.csatar::lang.plugin.component.structure.properties.mode.title',
                'description'       => 'csatar.csatar::lang.plugin.component.structure.properties.mode.description',
                'type'              => 'dropdown',
                'options'           => [
                    'accordion' => 'csatar.csatar::lang.plugin.component.structure.properties.mode.accordion',
                    'menu'      => 'csatar.csatar::lang.plugin.component.structure.properties.mode.menu',
                ],
            ],
        ];
    }

    public function onRun()
    {
        if (!empty($this->property('level'))) {
            $this->level = $this->property('level');
            $getterFunctionName = 'get'.$this->property('model_name').'sWithTree';
            $modelName = "Csatar\Csatar\Models\\" . $this->property('model_name');
            $this->structureArray = ($this->$getterFunctionName())->where('id', $this->property('model_id'));
            $this->showActiveScouts = true;
        } else {
            $this->displayHeader = true;
            $this->level = 1;
            $this->structureArray = $this->getAssociationsWithTree();
            $modelName = "Csatar\Csatar\Models\Association";
            $this->showActiveScouts = false;
        }

        $model = $modelName::find($this->property('model_id'));
        if(isset(Auth::user()->scout)) {
            $this->permissions = Auth::user()->scout->getRightsForModel($model);
        }

        $this->mode = $this->property('mode');
    }

    public function getAssociationsWithTree() {
        return collect(Cache::rememberForever('associations', function() {
            return Association::with([
                'districtsActive.teamsActive.troopsActive.patrolsActive.scoutsActive' => function($query) {
                    return $query->select('csatar_csatar_scouts.id', 'csatar_csatar_scouts.legal_relationship_id', 'csatar_csatar_scouts.family_name', 'csatar_csatar_scouts.given_name', 'csatar_csatar_scouts.ecset_code', 'csatar_csatar_scouts.patrol_id');
                },
                'districtsActive.teamsActive.troopsActive.scoutsActive' => function($query) {
                    return $query->select('csatar_csatar_scouts.id', 'csatar_csatar_scouts.legal_relationship_id', 'csatar_csatar_scouts.family_name', 'csatar_csatar_scouts.given_name', 'csatar_csatar_scouts.ecset_code', 'csatar_csatar_scouts.troop_id');
                },
                'districtsActive.teamsActive.patrolsActive.scoutsActive' => function($query) {
                    return $query->select('csatar_csatar_scouts.id', 'csatar_csatar_scouts.legal_relationship_id', 'csatar_csatar_scouts.family_name', 'csatar_csatar_scouts.given_name', 'csatar_csatar_scouts.ecset_code', 'csatar_csatar_scouts.patrol_id');
                },
                'districtsActive.teamsActive.scoutsActive' => function($query) {
                    return $query->select('csatar_csatar_scouts.id', 'csatar_csatar_scouts.legal_relationship_id', 'csatar_csatar_scouts.family_name', 'csatar_csatar_scouts.given_name', 'csatar_csatar_scouts.ecset_code', 'csatar_csatar_scouts.team_id');
                },
            ])
                ->get()->toArray();
        }));
    }

    public function getDistrictsWithTree() {
        return collect(Cache::rememberForever('districts', function() {
            return District::with([
                'teamsActive.troopsActive.patrolsActive.scoutsActive' => function($query) {
                    return $query->select('csatar_csatar_scouts.id', 'csatar_csatar_scouts.legal_relationship_id', 'csatar_csatar_scouts.family_name', 'csatar_csatar_scouts.given_name', 'csatar_csatar_scouts.ecset_code', 'csatar_csatar_scouts.patrol_id');
                },
                'teamsActive.troopsActive.scoutsActive' => function($query) {
                    return $query->select('csatar_csatar_scouts.id', 'csatar_csatar_scouts.legal_relationship_id', 'csatar_csatar_scouts.family_name', 'csatar_csatar_scouts.given_name', 'csatar_csatar_scouts.ecset_code', 'csatar_csatar_scouts.troop_id');
                },
                'teamsActive.patrolsActive.scoutsActive' => function($query) {
                    return $query->select('csatar_csatar_scouts.id', 'csatar_csatar_scouts.legal_relationship_id', 'csatar_csatar_scouts.family_name', 'csatar_csatar_scouts.given_name', 'csatar_csatar_scouts.ecset_code', 'csatar_csatar_scouts.patrol_id');
                },
                'teamsActive.scoutsActive' => function($query) {
                    return $query->select('csatar_csatar_scouts.id', 'csatar_csatar_scouts.legal_relationship_id', 'csatar_csatar_scouts.family_name', 'csatar_csatar_scouts.given_name', 'csatar_csatar_scouts.ecset_code', 'csatar_csatar_scouts.team_id');
                },
            ])
            ->get()->toArray();
        }));
    }

    public function getTeamsWithTree() {
        return collect(Cache::rememberForever('teams', function() {
            return Team::with([
                'troopsActive.patrolsActive.scoutsActive' => function($query) {
                    return $query->select('csatar_csatar_scouts.id', 'csatar_csatar_scouts.legal_relationship_id', 'csatar_csatar_scouts.family_name', 'csatar_csatar_scouts.given_name', 'csatar_csatar_scouts.ecset_code', 'csatar_csatar_scouts.patrol_id');
                },
                'troopsActive.scoutsActive' => function($query) {
                    return $query->select('csatar_csatar_scouts.id', 'csatar_csatar_scouts.legal_relationship_id', 'csatar_csatar_scouts.family_name', 'csatar_csatar_scouts.given_name', 'csatar_csatar_scouts.ecset_code', 'csatar_csatar_scouts.troop_id');
                },
                'patrolsActive.scoutsActive' => function($query) {
                    return $query->select('csatar_csatar_scouts.id', 'csatar_csatar_scouts.legal_relationship_id', 'csatar_csatar_scouts.family_name', 'csatar_csatar_scouts.given_name', 'csatar_csatar_scouts.ecset_code', 'csatar_csatar_scouts.patrol_id');
                },
                'scoutsActive' => function($query) {
                    return $query->select('csatar_csatar_scouts.id', 'csatar_csatar_scouts.legal_relationship_id', 'csatar_csatar_scouts.family_name', 'csatar_csatar_scouts.given_name', 'csatar_csatar_scouts.ecset_code', 'csatar_csatar_scouts.team_id');
                },
            ])
                ->get()->toArray();
        }));
    }

    public function getTroopsWithTree() {
        return collect(Cache::rememberForever('troops', function() {
            return Troop::with([
                'patrolsActive.scoutsActive' => function($query) {
                    return $query->select('csatar_csatar_scouts.id', 'csatar_csatar_scouts.legal_relationship_id', 'csatar_csatar_scouts.family_name', 'csatar_csatar_scouts.given_name', 'csatar_csatar_scouts.ecset_code', 'csatar_csatar_scouts.patrol_id');
                },
                'scoutsActive' => function($query) {
                    return $query->select('csatar_csatar_scouts.id', 'csatar_csatar_scouts.legal_relationship_id', 'csatar_csatar_scouts.family_name', 'csatar_csatar_scouts.given_name', 'csatar_csatar_scouts.ecset_code', 'csatar_csatar_scouts.troop_id');
                },
            ])
                ->get()->toArray();
        }));
    }

    public function getPatrolsWithTree() {
        return collect(Cache::rememberForever('patrols', function() {
            return Patrol::with([
                'scoutsActive' => function($query) {
                    return $query->select('csatar_csatar_scouts.id', 'csatar_csatar_scouts.legal_relationship_id', 'csatar_csatar_scouts.family_name', 'csatar_csatar_scouts.given_name', 'csatar_csatar_scouts.ecset_code', 'csatar_csatar_scouts.patrol_id');
                },
            ])
                ->get()->toArray();
        }));
    }

    public function getScouts() {
        return collect(Cache::rememberForever('scouts', function() {
            return Scout::all()->toArray();
        }));
    }
}
