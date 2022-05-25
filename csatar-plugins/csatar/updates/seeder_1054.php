<?php namespace Csatar\Csatar\Updates;

use Seeder;
use Csatar\Csatar\Models\Association;

class Seeder1054 extends Seeder
{
    public function run()
    {
        $association = Association::where('name', 'Horvátországi magyar cserkészek')->first();
        $association->ecset_code_suffix = 'H';
        $association->save();

        $association = Association::where('name', 'Kárpátaljai Magyar Cserkészszövetség')->first();
        $association->ecset_code_suffix = 'KÁ';
        $association->save();

        $association = Association::where('name', 'Külföldi Magyar Cserkészszövetség')->first();
        $association->ecset_code_suffix = 'KÜ';
        $association->save();

        $association = Association::where('name', 'Magyar Cserkészszövetség')->first();
        $association->ecset_code_suffix = 'M';
        $association->save();

        $association = Association::where('name', 'Romániai Magyar Cserkészszövetség')->first();
        $association->ecset_code_suffix = 'E';
        $association->bank_account = null;
        $association->save();

        $association = Association::where('name', 'Szlovákiai Magyar Cserkészszövetség')->first();
        $association->ecset_code_suffix = 'F';
        $association->save();

        $association = Association::where('name', 'Vajdasági Magyar Cserkészszövetség')->first();
        $association->ecset_code_suffix = 'D';
        $association->save();
    }
}
