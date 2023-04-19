<?php

namespace Csatar\Csatar\Classes;

use Cache;
use October\Rain\Database\Collection;
use Csatar\Csatar\Models\Association;
use Csatar\Csatar\Models\District;
use Csatar\Csatar\Models\Patrol;
use Csatar\Csatar\Models\Scout;
use Csatar\Csatar\Models\Team;
use Csatar\Csatar\Models\Troop;
use Illuminate\Contracts\Support\Arrayable;

class StructureTree
{
    private static function getAssociationsQueryWithArray(): array
    {
        return [
            'districtsActive' => function($query) {
                return self::selectFromDistricts($query);
            },
            'districtsActive.teamsActive' => function($query) {
                return self::selectFromTeams($query);
            },
            'districtsActive.teamsActive.troopsActive' => function($query) {
                return self::selectFromTroops($query);
            },
            'districtsActive.teamsActive.troopsActive.patrolsActive' => function($query) {
                return self::selectFromPatrols($query);
            },
            'districtsActive.teamsActive.troopsActive.patrolsActive.scoutsActive' => function($query) {
                return self::selectFromScouts($query);
            },
            'districtsActive.teamsActive.troopsActive.patrolsActive.scoutsActive.legal_relationship' => function($query) {
                return self::selectFromLegalRelationship($query);
            },
            'districtsActive.teamsActive.troopsActive.scoutsActive' => function($query) {
                return self::selectFromScouts($query);
            },
            'districtsActive.teamsActive.troopsActive.scoutsActive.legal_relationship' => function($query) {
                return self::selectFromLegalRelationship($query);
            },
            'districtsActive.teamsActive.patrolsActive' => function($query) {
                return self::selectFromPatrols($query);
            },
            'districtsActive.teamsActive.patrolsActive.scoutsActive' => function($query) {
                return self::selectFromScouts($query);
            },
            'districtsActive.teamsActive.patrolsActive.scoutsActive.legal_relationship' => function($query) {
                return self::selectFromLegalRelationship($query);
            },
            'districtsActive.teamsActive.scoutsActive' => function($query) {
                return self::selectFromScouts($query);
            },
            'districtsActive.teamsActive.scoutsInactive' => function($query) {
                return self::selectFromScouts($query);
            },
            'districtsActive.teamsActive.scoutsActive.legal_relationship' => function($query) {
                return self::selectFromLegalRelationship($query);
            },
        ];
    }

    public static function getStructureTree() {
        return collect(Cache::rememberForever('structureTree', function() {
            return self::toKeyedByIdArray(
               Association::with(self::getAssociationsQueryWithArray())
                    ->select('id', 'name', 'name_abbreviation')
                    ->get()
                );
        }));
    }

    public static function updateStructureTree() {
        $oldValue = Cache::pull('structureTree');
        $newValue = Cache::forever('structureTree', self::getStructureTree());
        return self::getStructureTree();
    }

    public static function toKeyedByIdArray($collection)
    {
        return $collection->map(function ($value) {
            $array = $value instanceof Arrayable ? $value->attributesToArray() : $value;
            if (!empty($value->getRelations())) {
                foreach ($value->getRelations() as $relationName => $relationItems) {
                    $array[$relationName] =
                        $relationItems instanceof Collection ? self::toKeyedByIdArray($relationItems) : ($relationItems instanceof Arrayable ? $relationItems->toArray() : $relationItems);
                }
            }

            return $array;
        })->keyBy('id')->toArray();
    }

    public static function selectFromDistricts($query) {
        return $query->select(
            'csatar_csatar_districts.id',
            'csatar_csatar_districts.name',
            'csatar_csatar_districts.status',
            'csatar_csatar_districts.association_id'
        );
    }

    public static function selectFromTeams($query) {
        return $query->select(
            'csatar_csatar_teams.id',
            'csatar_csatar_teams.team_number',
            'csatar_csatar_teams.name',
            'csatar_csatar_teams.status',
            'csatar_csatar_teams.district_id'
        );
    }

    public static function selectFromTroops($query) {
        return $query->select(
            'csatar_csatar_troops.id',
            'csatar_csatar_troops.name',
            'csatar_csatar_troops.status',
            'csatar_csatar_troops.team_id'
        );
    }

    public static function selectFromPatrols($query) {
        return $query->select(
            'csatar_csatar_patrols.id',
            'csatar_csatar_patrols.name',
            'csatar_csatar_patrols.status',
            'csatar_csatar_patrols.troop_id',
            'csatar_csatar_patrols.team_id'
        );
    }

    public static function selectFromScouts($query) {
        return $query->select(
            'csatar_csatar_scouts.id',
            'csatar_csatar_scouts.ecset_code',
            'csatar_csatar_scouts.family_name',
            'csatar_csatar_scouts.given_name',
            'csatar_csatar_scouts.legal_relationship_id',
            'csatar_csatar_scouts.team_id',
            'csatar_csatar_scouts.troop_id',
            'csatar_csatar_scouts.patrol_id',
            'csatar_csatar_scouts.inactivated_at',
        );
    }

    public static function selectFromLegalRelationship($query) {
        return $query->select(
            'csatar_csatar_legal_relationships.id',
            'csatar_csatar_legal_relationships.name'
        );
    }

    public static function getAssociationsWithTree() {
        return StructureTree::getStructureTree();
    }

    public static function getAssociationScoutsCount($associationId) {
        return StructureTree::getStructureTree()
            ->where('id', $associationId)
            ->pluck('districtsActive.*.teamsActive.*.scoutsActive')
            ->collapse()
            ->collapse()
            ->count();
    }

    public static function getDistrictsWithTree() {
        return StructureTree::getStructureTree()->pluck('districtsActive')->collapse()->keyBy('id');
    }

    public static function getDistrictScoutsCount($districtId) {
        return StructureTree::getStructureTree()
            ->pluck('districtsActive')
            ->collapse()
            ->where('id', $districtId)
            ->pluck('teamsActive.*.scoutsActive')
            ->collapse()
            ->collapse()
            ->count();
    }

    public static function getTeamsWithTree() {
        return StructureTree::getStructureTree()->pluck('districtsActive.*.teamsActive')->collapse()->collapse()->keyBy('id');
    }

    public static function getTeamScoutsCount($teamId) {
        return StructureTree::getStructureTree()
            ->pluck('districtsActive.*.teamsActive')
            ->collapse()
            ->collapse()
            ->where('id', $teamId)
            ->pluck('scoutsActive')
            ->collapse()
            ->count();
    }

    public static function getTroopsWithTree() {
        return StructureTree::getStructureTree()->pluck('districtsActive.*.teamsActive.*.troopsActive')->collapse()->collapse()->keyBy('id');
    }

    public static function getTroopScoutsCount($troopId) {
        return StructureTree::getStructureTree()
            ->pluck('districtsActive.*.teamsActive.*.troopsActive')
            ->collapse()
            ->collapse()
            ->where('id', $troopId)
            ->pluck('scoutsActive')
            ->collapse()
            ->count();
    }

    public static function getPatrolsWithTree() {
        return StructureTree::getStructureTree()->pluck('districtsActive.*.teamsActive.*.patrolsActive')->collapse()->collapse()->keyBy('id');
    }

    public static function getPatrolScoutsCount($patrolId) {
        return StructureTree::getStructureTree()
            ->pluck('districtsActive.*.teamsActive.*.patrolsActive')
            ->collapse()
            ->collapse()
            ->where('id', $patrolId)
            ->pluck('scoutsActive')
            ->collapse()
            ->count();
    }

    public static function updateAssociationTree($associationId) {
        $query = Association::where('id', $associationId)
            ->with(self::getAssociationsQueryWithArray())
            ->select('id', 'name', 'name_abbreviation');

        $refreshedAssociation = self::toKeyedByIdArray($query->get()); // dd($refreshedAssociation);
        $refreshedAssociation = array_merge([], ...$refreshedAssociation);

        if (empty($refreshedAssociation)) {
            return;
        }

        $structureTree = Cache::pull('structureTree');
            if (empty($structureTree)) {
                StructureTree::getStructureTree();
                return;
            }

        $structureTree[$associationId] = $refreshedAssociation;
        Cache::forever('structureTree', $structureTree);
    }

    public static function updateDistrictTree($districtId) {
        $query = District::where('id', $districtId)->with([
            'teamsActive' => function($query) {
                return self::selectFromTeams($query);
            },
            'teamsActive.scoutsActive' => function($query) {
                return self::selectFromScouts($query);
            },
            'teamsActive.scoutsInactive' => function($query) {
                return self::selectFromScouts($query);
            },
            'teamsActive.scoutsActive.legal_relationship' => function($query) {
                return self::selectFromLegalRelationship($query);
            },
            'teamsActive.patrolsActive' => function($query) {
                return self::selectFromPatrols($query);
            },
            'teamsActive.patrolsActive.scoutsActive' => function($query) {
                return self::selectFromScouts($query);
            },
            'teamsActive.patrolsActive.scoutsActive.legal_relationship' => function($query) {
                return self::selectFromLegalRelationship($query);
            },
            'teamsActive.troopsActive' => function($query) {
                return self::selectFromTroops($query);
            },
            'teamsActive.troopsActive.scoutsActive' => function($query) {
                return self::selectFromScouts($query);
            },
            'teamsActive.troopsActive.scoutsActive.legal_relationship' => function($query) {
                return self::selectFromLegalRelationship($query);
            },
            'teamsActive.troopsActive.patrolsActive' => function($query) {
                return self::selectFromPatrols($query);
            },
            'teamsActive.troopsActive.patrolsActive.scoutsActive' => function($query) {
                return self::selectFromScouts($query);
            },
            'teamsActive.troopsActive.patrolsActive.scoutsActive.legal_relationship' => function($query) {
                return self::selectFromLegalRelationship($query);
            },
        ]);
        $refreshedDistrict = self::toKeyedByIdArray(self::selectFromDistricts($query)->get());
        $refreshedDistrict = array_merge([], ...$refreshedDistrict);
        if (empty($refreshedDistrict)) {
           return;
        }

        // get old tree from cache and empty cache
        $structureTree = Cache::pull('structureTree');
            if (empty($structureTree)) {
                StructureTree::getStructureTree();
                return;
            }

        // update the tree
        $structureTree[$refreshedDistrict['association_id']]['districtsActive'][$refreshedDistrict['id']] = $refreshedDistrict;
        // insert the tree back to cache
        Cache::forever('structureTree', $structureTree);
    }

    public static function updateTeamTree($teamId) {
        $query         = Team::where('id', $teamId)->with([
            'scoutsActive' => function($query) {
                return self::selectFromScouts($query);
            },
            'scoutsInactive' => function($query) {
                return self::selectFromScouts($query);
            },
            'scoutsActive.legal_relationship' => function($query) {
                return self::selectFromLegalRelationship($query);
            },
            'patrolsActive' => function($query) {
                return self::selectFromPatrols($query);
            },
            'patrolsActive.scoutsActive' => function($query) {
                return self::selectFromScouts($query);
            },
            'patrolsActive.scoutsActive.legal_relationship' => function($query) {
                return self::selectFromLegalRelationship($query);
            },
            'troopsActive' => function($query) {
                return self::selectFromTroops($query);
            },
            'troopsActive.scoutsActive' => function($query) {
                return self::selectFromScouts($query);
            },
            'troopsActive.scoutsActive.legal_relationship' => function($query) {
                return self::selectFromLegalRelationship($query);
            },
            'troopsActive.patrolsActive' => function($query) {
                return self::selectFromPatrols($query);
            },
            'troopsActive.patrolsActive.scoutsActive' => function($query) {
                return self::selectFromScouts($query);
            },
            'troopsActive.patrolsActive.scoutsActive.legal_relationship' => function($query) {
                return self::selectFromLegalRelationship($query);
            },
            'district' => function($query) {
                return self::selectFromDistricts($query);
            },
        ]);
        $refreshedTeam = self::toKeyedByIdArray(self::selectFromTeams($query)->get());
        $refreshedTeam = array_merge([], ...$refreshedTeam);
        if (empty($refreshedTeam)) {
            return;
        }

        // get old tree from cache and empty cache
        $structureTree = Cache::pull('structureTree');
        if (empty($structureTree)) {
            StructureTree::getStructureTree();
            return;
        }

        // update the tree
        $associationId = $refreshedTeam['district']['association_id'];
        $districtId    = $refreshedTeam['district']['id'];
        unset($refreshedTeam['district']);
        $structureTree[$associationId]['districtsActive'][$districtId]['teamsActive'][$refreshedTeam['id']] = $refreshedTeam;

        // insert the tree back to cache
        Cache::forever('structureTree', $structureTree);
    }
}
