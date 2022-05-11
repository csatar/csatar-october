<?php namespace Csatar\Forms\Updates;

use Seeder;
use Csatar\Forms\Models\Form;

class Seeder104 extends Seeder
{
    public function run()
    {
        $district = Form::create([
            'title' => 'Körzet',
            'model' => 'Csatar\Csatar\Models\District',
        ]);
    }
}
