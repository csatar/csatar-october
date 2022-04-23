<?php namespace Csatar\Csatar\Updates;

use Seeder;
use Csatar\Csatar\Models\ChronicIllness;
use Csatar\Csatar\Models\SpecialDiet;
use Csatar\Csatar\Models\Religion;
use Csatar\Csatar\Models\TShirtSize;

class Seeder1028 extends Seeder
{
    public function run()
    {
        $specialDiets = [
            'Sporttáplálkozás',
            'Gluténmentes',
            'Szénhidrátmentes',
            'Laktózmentes',
            'Paleo',
            'Vegán',
            'Vegetáriánus'
        ];
        
        foreach($specialDiets as $title) {
            $specialDiets = SpecialDiet::create([
                'title' => $title
            ]);
        }
        
        $religions = [
            'Adventista',
            'Baptista',
            'Evangélikus',
            'Görög katolikus',
            'Jehova tanúi',
            'Muzulmán',
            'Ortodox',
            'Református',
            'Római katolikus',
            'Unitárius',
            'Más felekezethez tartozó'
        ];
        
        foreach($religions as $title) {
            $religions = Religion::create([
                'title' => $title
            ]);
        }
        
        $tshirtSizes = [
            '4',
            '6',
            '8',
            '10',
            '12',
            'XS',
            'S',
            'M',
            'L',
            'XL',
            'XXL',
            '3XL',
            '4XL',
            '5XL'
        ];
        
        foreach($tshirtSizes as $title) {
            $tshirtSizes = TShirtSize::create([
                'title' => $title
            ]);
        }
        
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