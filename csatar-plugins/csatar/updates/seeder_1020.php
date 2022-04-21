<?php namespace Csatar\Csatar\Updates;

use Seeder;
use Csatar\Csatar\Models\Allergy;

class Seeder1020 extends Seeder
{
    public function run()
    {
        $allergies = [
            'Ételintollerancia',
            'Ételallergiák',
            'Pollen alergia/Szénanátha',
            'Belélegzései allergia',
            'Rovarméreg allergia',
            'Kontakt allergia (vegyszerekre, anyagokra)',
            'Gyógyszerallergia',
            'Egyéb'
        ];
        
        foreach($allergies as $title) {
            $allergies = Allergy::create([
                'title' => $title
            ]);
        }
    }
}