<?php namespace Csatar\Csatar\Components;

use Auth;
use Lang;
use Cms\Classes\ComponentBase;
use Redirect;

class OrganizationUnitFrontend extends ComponentBase
{
    public $model;
    public $content_page;
    public $permissions;

    public function componentDetails()
    {
        return [
            'name' => Lang::get('csatar.csatar::lang.plugin.component.organizationUnitFrontend.name'),
            'description' => Lang::get('csatar.csatar::lang.plugin.component.organizationUnitFrontend.description'),
        ];
    }

    public function defineProperties()
    {
        return [
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
        $modelName = "Csatar\Csatar\Models\\" . $this->property('model_name');
        if (is_numeric($this->property('model_id'))) {
            $this->model = $modelName::find($this->property('model_id'));
            if(Auth::user()->scout) {
                $this->permissions = Auth::user()->scout->getRightsForModel($this->model);
            }
            if (empty($this->model->content_page))
            {
                $this->content_page = $this->model->content_page()->create([
                    'title' => '',
                    'content' => ''
                ]);
            } else {
                $this->content_page = $this->model->content_page;
            }
        }
    }

    public function onEditContent()
    {
        $modelName = "Csatar\Csatar\Models\\" . $this->property('model_name');
        $model = $modelName::find($this->property('model_id'));

        $content = $model->content_page;
        return [
            '#content' => $this->renderPartial('@editor', ['content_page' => $content])
        ];
    }

    public function onSaveContent()
    {
        $modelName = "Csatar\Csatar\Models\\" . $this->property('model_name');
        $model = $modelName::find($this->property('model_id'));

        $content = $model->content_page;
        $content->title = post('title');
        $content->content = post('content');
        $content->save();

        return Redirect::refresh();
    }
}
