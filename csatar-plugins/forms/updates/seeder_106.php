<?php namespace Csatar\Forms\Updates;

use Seeder;
use Csatar\Forms\Models\Form;

class Seeder106 extends Seeder
{
    public function run()
    {
        $team = Form::create([
            'title' => 'Raj',
            'model' => 'Csatar\Csatar\Models\Troop',
        ]);
    }
}