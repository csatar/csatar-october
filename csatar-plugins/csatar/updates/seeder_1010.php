<?php namespace Csatar\Csatar\Updates;

use Seeder;
use Csatar\Csatar\Models\LegalRelationship;

class Seeder1010 extends Seeder
{
    public function run()
    {
        $legalRelationships = [
            'Alakuló csapat tag',
            'Újonc',
            'Tag',
            'Tiszteletbeli tag'
        ];
        
        for($i = 0; $i < count($legalRelationships); ++$i) {
            $legalRelationship = LegalRelationship::create([
                'title' => $legalRelationships[$i],
                'sort_order' => $i + 1
            ]);
        }
    }
}
