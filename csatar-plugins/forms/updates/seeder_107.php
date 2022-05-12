<?php namespace Csatar\Forms\Updates;

use Seeder;
use Csatar\Forms\Models\Form;

class Seeder107 extends Seeder
{
    public function run()
    {
        $team = Form::create([
            'title' => 'Őrs',
            'model' => 'Csatar\Csatar\Models\Patrol',
        ]);
    }
}
