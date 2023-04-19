<?php

namespace Csatar\KnowledgeRepository\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Csatar\Forms\Components\BasicForm;
use Csatar\Csatar\Components\Partials;
use Lang;
use Redirect;

class MethodologyForm extends ComponentBase
{
    public $basicForm;

    public function init()
    {
        $this->basicForm = $this->addComponent(BasicForm::class, 'basicForm', [
            'formSlug' => 'modszertan',
            'recordKeyParam' => 'id',
            'recordActionParam' => 'action',
            'createRecordKeyword' => 'letrehozas',
            'actionUpdateKeyword' => 'modositas',
        ]);

        $this->addComponent(Partials::class, 'partials', []);
    }

    public function componentDetails()
    {
        return [
            'name' => Lang::get('csatar.knowledgerepository::lang.plugin.components.methodologyForm.name'),
            'description' => Lang::get('csatar.knowledgerepository::lang.plugin.components.methodologyForm.description'),
        ];
    }

    public function onRender()
    {
        $this->basicForm->additionalData = $this->renderPartial('@additionalData.htm');
        $this->basicForm->onRun();
    }

    public function onApproveMethodology()
    {
        $user = Auth::user();
        if (!$user || empty($user->scout)) {
            return;
        }

        $methodology = $this->basicForm->record;
        $methodology->approved_at          = date('Y-m-d H:i:s');
        $methodology->approver_csatar_code = $user->scout->ecset_code;
        $methodology->save();

        $this->onRender();
        return Redirect::refresh();
    }

    public function onDelete()
    {
        $this->basicForm->record->delete();

        return Redirect::to('/tudastar/modszertanok');
    }

}
