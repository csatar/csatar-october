<?php namespace Csatar\Csatar\Components;

use Auth;
use Cache;
use Lang;
use Cms\Classes\ComponentBase;
use Csatar\Csatar\Classes\StructureTree;
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
            $this->structureArray = (StructureTree::$getterFunctionName())->where('id', $this->property('model_id'));
            $this->showActiveScouts = true;
        } else {
            $this->displayHeader = true;
            $this->level = 1;
            $this->structureArray = StructureTree::getAssociationsWithTree();
            $modelName = "Csatar\Csatar\Models\Association";
            $this->showActiveScouts = false;
        }

        $model = $modelName::find($this->property('model_id'));
        if(isset(Auth::user()->scout)) {
            $this->permissions = Auth::user()->scout->getRightsForModel($model);
        }

        $this->mode = $this->property('mode');
    }

    public function getAssociationScoutsCount($associationId)
    {
        return StructureTree::getAssociationScoutsCount($associationId);
    }

    public static function getDistrictScoutsCount($districtId) {
        return StructureTree::getDistrictScoutsCount($districtId);
    }
}
