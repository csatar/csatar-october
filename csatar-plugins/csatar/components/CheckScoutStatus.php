<?php namespace Csatar\Csatar\Components;

use Csatar\Csatar\Models\Scout;
use Csatar\Csatar\Models\Team;
use Lang;
use Input;
use Cms\Classes\ComponentBase;
use Redirect;
use Response;
use Validator;
use ValidationException;

class CheckScoutStatus extends ComponentBase
{
    public $scoutCode;

    public $json;

    public function componentDetails()
    {
        return [
            'name' => Lang::get('csatar.csatar::lang.plugin.component.checkScoutStatus.name'),
            'description' => Lang::get('csatar.csatar::lang.plugin.component.checkScoutStatus.description'),
        ];
    }

    public function defineProperties()
    {
        return [
            'scoutCode' => [
                'title'             => 'csatar.csatar::lang.plugin.component.checkScoutStatus.scoutCode.title',
                'description'       => 'csatar.csatar::lang.plugin.component.checkScoutStatus.scoutCode.description',
                'type'              => 'string',
                'default'           => '{{ :ecset_code }}'
            ]
        ];
    }

    public function onRender()
    {
        if (empty($this->property('scoutCode'))) {
            return $this->renderPartial('@default', ['code' => -1]);
        }

        $this->scoutCode = $this->property('scoutCode');
        $this->json = false;

        if (Input::get('json') === 'true' || Input::get('json') === '1')
        {
            $this->json = true;
        }

        return $this->onGetScoutStatus();
    }

    public function onGetScoutStatus()
    {
        $scout = Scout::where('ecset_code', $this->scoutCode)->get()->first();

        if (!$scout) {
            return $this->renderPartial('@default', ['is_exists' => false]);
        }

        $team = Team::where('id', $scout->team_id)->get()->first();

        $variablesToPass = [
            'code' =>   $this->scoutCode,
            'is_active' => $scout->is_active,
            'is_exists' => true,
            'team_id' => $team->id,
            'team_name' => $team->name,
            'team_number' => $team->team_number,
        ];

        if ($this->json) {
            return Redirect::refresh()->with('ecset_json', $variablesToPass);
        }

        return $this->renderPartial('@default', $variablesToPass);
    }

    public function onGetOtherScoutStatus()
    {
        $data = post();
        $rules = ['ecsk_code' => 'required'];

        $validation = Validator::make(
            $data,
            $rules
        );

        if ($validation->fails()) {
            throw new ValidationException($validation);
        }

        return Redirect::to('/tag-lekerdezes/'. $data['ecsk_code']);
    }
}
