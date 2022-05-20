<?php namespace Csatar\Csatar\Updates;

use Seeder;
use Csatar\Csatar\Models\Promise;

class Seeder1059 extends Seeder
{
    public function run()
    {
        $promises = [
            'Kiscserkész igéret',
            'Cserkész fogadalom',
        ];
        
        foreach($promises as $name) {
            $promise = Promise::create([
                'name' => $name
            ]);
        }
    }
}