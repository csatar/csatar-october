<?php namespace Csatar\Csatar\Updates;

use Seeder;
use Csatar\Csatar\Models\Association;
use Csatar\Csatar\Models\District;
use Csatar\Csatar\Models\Team;

class TestData extends Seeder
{
    public function run()
    {
        // districts
        $association_magyar = Association::where('name', 'Magyar Cserkészszövetség')->first();
        if (isset($association_magyar)) {
            $district_1 = District::firstOrNew([
                'name' => 'Nógrád',
                'association_id' => $association_magyar->id,
            ]);
            $district_1->address = 'Balassagyarmat, Jácint utca, 21';
            $district_1->phone = '00361223454';
            $district_1->email = 'erika@yahoo.com';
            $district_1->contact_name = 'Vass Erika';
            $district_1->contact_email = 'erika@yahoo.com';
            $district_1->leadership_presentation = 'A';
            $district_1->description = 'A';
            $district_1->save();
        }

        // teams
        if (isset($district_1)) {
            $team_1 = Team::firstOrNew([
                'name' => 'Nógrádi első próba csapat',
                'district_id' => $district_1->id,
            ]);
            $team_1->team_number = '1';
            $team_1->address = 'Balassagyarmat, Ady Endre utca, 10';
            $team_1->foundation_date = '2000-06-06';
            $team_1->phone = '0877665';
            $team_1->email = 'edina@yahoo.com';
            $team_1->contact_name = 'Edina';
            $team_1->contact_email = 'edina@yahoo.com';
            $team_1->leadership_presentation = 'A';
            $team_1->description = 'A';
            $team_1->juridical_person_name = 'Edina';
            $team_1->juridical_person_address = 'Balassagyarmat, Ady Endre utca, 10';
            $team_1->juridical_person_tax_number = '06548';
            $team_1->juridical_person_bank_account = 'EM66544';
            $team_1->save();
        }
    }
}
