<?php namespace Csatar\Csatar\Updates;

use Seeder;
use Csatar\Csatar\Models\ChronicIllness;

class Seeder1012 extends Seeder
{
    public function run()
    {
        $chronicIllnesses = [
            'Magas vérnyomás',
            'Szívelégtelenség',
            'Allergia',
            'Cukorbetegség',
            'Mozgásszervi betegségek',
            'Pajzsmirigy működési zavar',
            'Schizophrenia, schizotypiás és paranoid zavarok',
            'Daganatos betegség',
            'Krónikus légzési elégtelenség',
            'Veseelégtelenség',
            'HIV/SIDA'
        ];
        
        foreach($chronicIllnesses as $title) {
            $chronicIllness = ChronicIllness::create([
                'title' => $title
            ]);
        }
    }
}