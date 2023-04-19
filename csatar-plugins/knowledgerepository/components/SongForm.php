<?php

namespace Csatar\KnowledgeRepository\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Csatar\Forms\Components\BasicForm;
use Csatar\Csatar\Components\Partials;
use Lang;
use Redirect;

class SongForm extends ComponentBase
{
    public $basicForm;

    public function init()
    {

        $this->basicForm = $this->addComponent(BasicForm::class, 'basicForm', [
            'formSlug' => 'dal',
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
            'name' => Lang::get('csatar.knowledgerepository::lang.plugin.components.songForm.name'),
            'description' => Lang::get('csatar.knowledgerepository::lang.plugin.components.songForm.description'),
        ];
    }

    public function onRender()
    {
        $this->basicForm->additionalData = $this->renderPartial('@additionalData.htm');
        $this->basicForm->onRun();
    }

    public function onApproveSong()
    {
        $user = Auth::user();
        if (!$user || empty($user->scout)) {
            return;
        }

        $song = $this->basicForm->record;
        $song->approved_at          = date('Y-m-d H:i:s');
        $song->approver_csatar_code = $user->scout->ecset_code;
        $song->save();

        $this->onRender();
        return Redirect::refresh();
    }

    public function onDelete()
    {
        $this->basicForm->record->delete();

        return Redirect::to('/tudastar/dalok');
    }
}
