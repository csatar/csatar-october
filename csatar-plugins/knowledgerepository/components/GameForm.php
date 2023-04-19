<?php

namespace Csatar\KnowledgeRepository\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Csatar\KnowledgeRepository\Models\Game;
use Csatar\Forms\Components\BasicForm;
use Csatar\Csatar\Components\Partials;
use Lang;
use Redirect;

class GameForm extends ComponentBase
{
    public $basicForm;

    public function init()
    {
        $this->basicForm = $this->addComponent(BasicForm::class, 'basicForm', [
            'formSlug' => 'jatek',
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
            'name' => Lang::get('csatar.knowledgerepository::lang.plugin.components.gameForm.name'),
            'description' => Lang::get('csatar.knowledgerepository::lang.plugin.components.gameForm.description'),
        ];
    }

    public function onRender()
    {
        $this->basicForm->additionalData = $this->renderPartial('@additionalData.htm');
        $this->basicForm->onRun();
    }

    public function onApproveGame()
    {
        $user = Auth::user();
        if (!$user || empty($user->scout)) {
            return;
        }

        $game = $this->basicForm->record;
        $game->approved_at          = date('Y-m-d H:i:s');
        $game->approver_csatar_code = $user->scout->ecset_code;
        $game->save();

        $this->onRender();
        return Redirect::refresh();
    }

    public function onDelete()
    {
        $this->basicForm->record->delete();

        return Redirect::to('/tudastar/jatekok');
    }

}
