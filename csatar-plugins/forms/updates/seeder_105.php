<?php namespace Csatar\Forms\Updates;

use Seeder;
use Csatar\Forms\Models\Form;

class Seeder105 extends Seeder
{
    public function run()
    {
        $team = Form::create([
            'title' => 'Csapat',
            'model' => 'Csatar\Csatar\Models\Team',
        ]);
    }
}
