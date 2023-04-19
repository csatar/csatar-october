<?php
namespace Csatar\Csatar\Updates;

use Csatar\Csatar\Models\Locations;
use RainLab\Builder\Classes\ComponentHelper;
use Seeder;
use Db;

class LocationData extends Seeder
{
    public function run()
    {
        // seed romanian locations
        set_time_limit(100000);

        if (($handle = fopen(base_path() . "/plugins/csatar/csatar/updates/locations_ro.csv", "r")) !== false) {
            while (($data = fgetcsv($handle)) !== false) {
                $location = Locations::firstOrCreate(                        [
                    'country'     => 'Romania',
                    'code'        => $data[0],
                    'county'      => $data[1],
                    'city'        => $data[2],
                    'street_type' => $data[3],
                ]);

                $location->street = preg_replace('/\s+nr\..*/i', '', $data[4]);
                $location->save();
            }
        }
    }
}
