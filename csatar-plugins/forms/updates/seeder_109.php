<?php namespace Csatar\Forms\Updates;

use Seeder;
use Csatar\Forms\Models\Form;

class Seeder109 extends Seeder
{
    public function run()
    {
        $forms = Form::all();

        foreach($forms as $form) {
            $form->slugAttributes();
            $form->save();
        }
    }
}
