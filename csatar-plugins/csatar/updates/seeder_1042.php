<?php namespace Csatar\Csatar\Updates;

use Seeder;
use Csatar\Csatar\Models\Association;

class Seeder1042 extends Seeder
{
    public function run()
    {
        $associations = [
            'Horvátországi magyar cserkészek',
            'Kárpátaljai Magyar Cserkészszövetség',
            'Külföldi Magyar Cserkészszövetség',
            'Magyar Cserkészszövetség',
            'Romániai Magyar Cserkészszövetség',
            'Szlovákiai Magyar Cserkészszövetség',
            'Vajdasági Magyar Cserkészszövetség',
        ];
        
        foreach($associations as $name) {
            $association = Association::create([
                'name' => $name,
                'contact_name' => 'Abcde',
                'contact_email' => 'ab@ab.ab',
                'address' => 'Abcde',
                'description' => 'A'
            ]);
        }
    }
}
