<?php namespace Csatar\Csatar\Updates;

use Seeder;
use Csatar\Csatar\Models\Hierarchy;

class Seeder1040 extends Seeder
{
    public function run()
    {
        $hierarchyItems = [
            'RMCSSZ',
            'Körzetvezető',
            'Csapatvezető',
            'Rajvezető',
            'Őrsvezető',
            'Cserkész',
        ];
        
        $idOfLastElement = null;
        for($i = 0; $i < count($hierarchyItems); ++$i) {
            $hierachyItem = Hierarchy::create([
                'name' => $hierarchyItems[$i],
                'parent_id' => $idOfLastElement,
                'sort_order' => $i + 1
            ]);
            $idOfLastElement = $hierachyItem->id;
        }
    }
}
