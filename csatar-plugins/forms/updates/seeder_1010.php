<?php namespace Csatar\Forms\Updates;

use Seeder;
use Csatar\Forms\Models\Form;

class Seeder1010 extends Seeder
{
    public function run()
    {
        $team = Form::create([
            'title' => 'Csapatjelentés',
            'model' => 'Csatar\Csatar\Models\TeamReport',
        ]);
    }
}