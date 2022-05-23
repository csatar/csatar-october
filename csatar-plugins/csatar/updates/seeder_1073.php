<?php namespace Csatar\Csatar\Updates;

use Seeder;
use Csatar\Csatar\Models\ProfessionalQualification;

class Seeder1073 extends Seeder
{
    public function run()
    {
        $professional_qualifications = [
            'Regős',
        ];
        
        foreach($professional_qualifications as $name) {
            $professional_qualification = ProfessionalQualification::create([
                'name' => $name
            ]);
        }
    }
}