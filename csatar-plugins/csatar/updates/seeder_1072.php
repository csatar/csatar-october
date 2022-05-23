<?php namespace Csatar\Csatar\Updates;

use Seeder;
use Csatar\Csatar\Models\SpecialTest;

class Seeder1072 extends Seeder
{
    public function run()
    {
        $special_tests = [
            'Szakács',
            'Rovás',
            'Arany toll díj',
            'Tűzrakó',
            'Tájékozódó',
            'Csomózó',
            'Három sastoll',
            'Elsősegélynyújtó',
            'Gombász',
            'Helyi idegenvezető',
            'Színész',
            'Honismereti',
            'Szállásmester',
            'Nótafa',
            'Zenész',
            'Fényképész',
            'Íródeák',
        ];
        
        foreach($special_tests as $name) {
            $special_test = SpecialTest::create([
                'name' => $name
            ]);
        }
    }
}