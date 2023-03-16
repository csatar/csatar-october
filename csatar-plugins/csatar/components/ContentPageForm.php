<?php

namespace Csatar\Csatar\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Csatar\Csatar\Components\Partials;
use Csatar\Forms\Components\BasicForm;
use Lang;
use RainLab\Builder\Classes\ComponentHelper;

class ContentPageForm extends ComponentBase
{
    public $basicForm;

    public function init()
    {
        $this->basicForm = $this->addComponent(BasicForm::class, 'contentPageFormForm', [
            'formSlug' => 'rolunk',
            'subForm' => true,
            'parentModel' => [
                'class' => $this->property('parentModelClass'),
                'recordKeyParam' => $this->property('parentRecordKeyParam'),
                'recordKeyValue' => $this->param($this->property('parentRecordKeyParam')),
            ],
            'getRecordFromParent' => 'content_page',
        ]);
    }

    public function onRender() {
        $this->basicForm->onRun();
    }

    public function componentDetails()
    {
        return [
            'name' => Lang::get('csatar.csatar::lang.plugin.component.contentPageForm.name'),
            'description' => Lang::get('csatar.csatar::lang.plugin.component.contentPageForm.description'),
        ];
    }

    public function defineProperties()
    {
        return [
            'parentModelClass' => [
                'title'             => 'csatar.csatar::lang.plugin.component.contentPageForm.parentModelClass',
                'type'              => 'dropdown',
                'showExternalParam' => false
            ],
            'parentRecordKeyParam' => [
                'title'             => 'csatar.csatar::lang.plugin.component.contentPageForm.parentRecordKeyParam',
                'description'       => 'csatar.csatar::lang.plugin.component.contentPageForm.parentRecordKeyParamDescription',
                'type'              => 'string',
                'default'           => 'id',
                'showExternalParam' => false,
                'validation'  => [
                    'required' => [
                        'message' => Lang::get('csatar.forms::lang.components.basicForm.properties.propertiesValidation.recordKeyNotSelected')
                    ]
                ]
            ],
        ];
    }

    public function getParentModelClassOptions()
    {
        return ComponentHelper::instance()->listGlobalModels();
    }
}
