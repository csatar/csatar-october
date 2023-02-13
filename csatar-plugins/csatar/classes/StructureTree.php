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
    public static function getAssociationsWithTree() {
//        return collect(Cache::rememberForever('associations', function() {
            return self::toArray(Association::with([
                'districtsActive' => function($query) {
                    return $query->select('csatar_csatar_districts.id', 'csatar_csatar_districts.name', 'csatar_csatar_districts.status', 'csatar_csatar_districts.association_id');
                },
                'districtsActive.teamsActive' => function($query) {
                    return self::queryTeams($query);
                },
                'districtsActive.teamsActive.troopsActive' => function($query) {
                    return self::queryTroops($query);
                },
                'districtsActive.teamsActive.troopsActive.patrolsActive' => function($query) {
                    return self::queryPatrols($query);
                },
                'districtsActive.teamsActive.troopsActive.patrolsActive.scoutsActive' => function($query) {
                    return self::queryScouts($query);
                },
                'districtsActive.teamsActive.troopsActive.patrolsActive.scoutsActive.legal_relationship' => function($query) {
                    return self::queryLegalRelationship($query);
                },
                'districtsActive.teamsActive.troopsActive.scoutsActive' => function($query) {
                    return self::queryScouts($query);
                },
                'districtsActive.teamsActive.troopsActive.scoutsActive.legal_relationship' => function($query) {
                    return self::queryLegalRelationship($query);
                },
                'districtsActive.teamsActive.patrolsActive' => function($query) {
                    return self::queryPatrols($query);
                },
                'districtsActive.teamsActive.patrolsActive.scoutsActive' => function($query) {
                    return self::queryScouts($query);
                },
                'districtsActive.teamsActive.patrolsActive.scoutsActive.legal_relationship' => function($query) {
                    return self::queryLegalRelationship($query);
                },
                'districtsActive.teamsActive.scoutsActive' => function($query) {
                    return self::queryScouts($query);
                },
                'districtsActive.teamsActive.scoutsActive.legal_relationship' => function($query) {
                    return self::queryLegalRelationship($query);
                },
            ])
            ->get());//->keyBy('id')->toArray();
//        }));
    }

    public static function toArray($collection)
    {
        return $collection->map(function ($value) {
            $array = $value instanceof Arrayable ? $value->attributesToArray() : $value;
            if(!empty($value->getRelations())){
                foreach ($value->getRelations() as $relationName => $relationItems) {
                    $array[$relationName] = $relationItems instanceof Collection ? self::toArray($relationItems) : $relationItems;
                }
            }
            return $array;
        })->keyBy('id')->toArray();
    }

    public static function keyByPrimaryKey($collection)
    {//return $collection;
        return $collection->map(function ($value) {
            if(!empty($value->getRelations()) && $value->id == 5){
                foreach ($value->getRelations() as $relationName => $relationItems) {
                   $value->relations[$relationName] = ['fasdfasdf'];
                }
            }
            return $value;
        })->keyBy('id');
    }

    public static function queryScouts($query) {
        return $query->select(
            'csatar_csatar_scouts.id',
            'csatar_csatar_scouts.ecset_code',
            'csatar_csatar_scouts.family_name',
            'csatar_csatar_scouts.given_name',
            'csatar_csatar_scouts.legal_relationship_id',
            'csatar_csatar_scouts.team_id',
            'csatar_csatar_scouts.troop_id',
            'csatar_csatar_scouts.patrol_id'
        );
    }

    public static function queryLegalRelationship($query) {
        return $query->select(
            'csatar_csatar_legal_relationships.id',
            'csatar_csatar_legal_relationships.name'
        );
    }

    //reconsider later
    public static function getTree() {
        return Association::with([
            'districtsActive' => function($query) {
                return $query->select('csatar_csatar_districts.id', 'csatar_csatar_districts.name', 'csatar_csatar_districts.status', 'csatar_csatar_districts.association_id')
                    ->with([
                        'teamsActive' => function($query) {
                            return (self::queryTeams($query))
                                ->with([
                                    'troopsActive' => function($query) {
                                        return (self::queryTroops($query))
                                            ->with([
                                                'patrolsActive' => function($query) {
                                                    return (self::queryPatrols($query))
                                                        ->with([
                                                            'scoutsActive' => function($query) {
                                                                return self::queryScouts($query)->with([
                                                                    'legal_relationship' => function($query) {
                                                                        return self::queryLegalRelationship($query);
                                                                    }
                                                                ]);
                                                            }
                                                        ]);
                                                },
                                                'scoutsActive' => function($query) {
                                                    return self::queryScouts($query)->with([
                                                        'legal_relationship' => function($query) {
                                                            return self::queryLegalRelationship($query);
                                                        }
                                                    ]);
                                                }
                                            ]);
                                    },
                                    'patrolsActive' => function($query) {
                                        return (self::queryPatrols($query))
                                            ->with([
                                                'scoutsActive' => function($query) {
                                                    return self::queryScouts($query)->with([
                                                        'legal_relationship' => function($query) {
                                                            return self::queryLegalRelationship($query);
                                                        }
                                                    ]);
                                                }
                                            ]);
                                    },
                                    'scoutsActive' => function($query) {
                                        return self::queryScouts($query)->with([
                                            'legal_relationship' => function($query) {
                                                return self::queryLegalRelationship($query);
                                            }
                                        ]);
                                    }
                                ]);
                        },
                    ]);
            },
        ])
        ->get()->toArray();
    }

    public static function queryTeams($query) {
        return $query->select(
            'csatar_csatar_teams.id',
            'csatar_csatar_teams.name',
            'csatar_csatar_teams.status',
            'csatar_csatar_teams.district_id'
        );
    }

    public static function queryTroops($query) {
        return $query->select(
            'csatar_csatar_troops.id',
            'csatar_csatar_troops.name',
            'csatar_csatar_troops.status',
            'csatar_csatar_troops.team_id'
        );
    }

    public static function queryPatrols($query) {
        return $query->select(
            'csatar_csatar_patrols.id',
            'csatar_csatar_patrols.name',
            'csatar_csatar_patrols.status',
            'csatar_csatar_patrols.troop_id',
            'csatar_csatar_patrols.team_id'
        );
    }
}
