<?php namespace Csatar\Csatar\Updates;

use Seeder;
use Csatar\Csatar\Models\LeadershipQualification;

class Seeder1074 extends Seeder
{
    public function run()
    {
        $leadership_qualifications = [
             'Segédőrsvezető képzés',
             'Őrsvezető képzés',
             'Felnőtt őrsvezető képzés',
             'Segédvezető képzés',
             'Cserkész vezető',
        ];
        
        foreach($leadership_qualifications as $name) {
            $leadership_qualification = LeadershipQualification::create([
                'name' => $name
            ]);
        }
    }
}