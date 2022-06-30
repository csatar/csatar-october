<?php namespace Csatar\Csatar\Components;

use Lang;
use Cms\Classes\ComponentBase;
use Csatar\Csatar\Models\Association;

class Structure extends ComponentBase
{
    public $structureArray;
    public $level;

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
            ]
        ];
    }

    public function onRun()
    {
        if (!empty($this->property('level'))) {
            $this->level = $this->property('level');
            $modelName = "Csatar\Csatar\Models\\" . $this->property('model_name');
            $this->structureArray = $modelName::where('id', $this->property('model_id'))->get();
        } else {
            $this->level = 1;
            $this->structureArray = Association::all();
        }
    }
}
