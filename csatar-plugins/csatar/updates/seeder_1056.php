<?php namespace Csatar\Csatar\Updates;

use Seeder;
use Csatar\Csatar\Models\FoodSensitivity;

class Seeder1056 extends Seeder
{
    public function run()
    {
        $foodSensitivities = [
            'liszt',
            'tejfehérje (kazein)',
            'tojás',
            'mogyoró',
            'szója',
            'dió',
            'kagyló',
            'eper',
        ];
        
        foreach($foodSensitivities as $name) {
            $foodSensitivity = FoodSensitivity::create([
                'name' => $name
            ]);
        }
    }
}