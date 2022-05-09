<?php namespace Csatar\Forms\Updates;

use Seeder;
use Csatar\Forms\Models\Form;

class Seeder103 extends Seeder
{
    public function run()
    {
        $scout = Form::create([
            'title' => 'Tag',
            'model' => 'Csatar\Csatar\Models\Scout',
        ]);
        $association = Form::create([
            'title' => 'Szövetség',
            'model' => 'Csatar\Csatar\Models\Association',
        ]);
    }
}
