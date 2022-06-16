<?php namespace Csatar\Csatar\Updates;

use Seeder;
use Csatar\Csatar\Models\Association;
use Csatar\Csatar\Models\District;
use Csatar\Csatar\Models\Patrol;
use Csatar\Csatar\Models\Team;
use Csatar\Csatar\Models\Troop;

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

        $association_rmcssz = Association::where('name', 'Romániai Magyar Cserkészszövetség')->first();
        if (isset($association_rmcssz)) {
            $district_2 = District::firstOrNew([
                'name' => 'Csík',
                'association_id' => $association_rmcssz->id,
            ]);
            $district_2->address = 'Abcde';
            $district_2->phone = '012345';
            $district_2->email = 'a@aa.com';
            $district_2->contact_name = 'Szőcs Szilveszter';
            $district_2->contact_email = 'a@aa.com';
            $district_2->leadership_presentation = '-';
            $district_2->description = '-';
            $district_2->save();

            $district_3 = District::firstOrNew([
                'name' => 'Észak-Erdély',
                'association_id' => $association_rmcssz->id,
            ]);
            $district_3->address = 'Abcde';
            $district_3->phone = '012345';
            $district_3->email = 'a@aa.com';
            $district_3->contact_name = 'Szénás Zalán';
            $district_3->contact_email = 'a@aa.com';
            $district_3->leadership_presentation = '-';
            $district_3->description = '-';
            $district_3->save();

            $district_4 = District::firstOrNew([
                'name' => 'Háromszék',
                'association_id' => $association_rmcssz->id,
            ]);
            $district_4->address = 'Abcde';
            $district_4->phone = '012345';
            $district_4->email = 'a@aa.com';
            $district_4->contact_name = 'Székely István';
            $district_4->contact_email = 'a@aa.com';
            $district_4->leadership_presentation = '-';
            $district_4->description = '-';
            $district_4->save();
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

        if (isset($district_2)) {
            $team_2 = Team::firstOrNew([
                'name' => 'Szent István',
                'district_id' => $district_2->id,
            ]);
            $team_2->team_number = '4';
            $team_2->address = 'Abcde';
            $team_2->foundation_date = '2000-06-18';
            $team_2->phone = '01234';
            $team_2->email = 'a@aa.com';
            $team_2->contact_name = 'Bálint Lajos Lóránt';
            $team_2->contact_email = 'a@aa.com';
            $team_2->leadership_presentation = '-';
            $team_2->description = '-';
            $team_2->juridical_person_name = 'Bálint Lajos Lóránt';
            $team_2->juridical_person_address = 'Abcde';
            $team_2->juridical_person_tax_number = '01234';
            $team_2->juridical_person_bank_account = '01234';
            $team_2->save();

            $team_3 = Team::firstOrNew([
                'name' => 'Zöld Péter',
                'district_id' => $district_2->id,
            ]);
            $team_3->team_number = '18';
            $team_3->address = 'Abcde';
            $team_3->foundation_date = '2000-06-18';
            $team_3->phone = '01234';
            $team_3->email = 'a@aa.com';
            $team_3->contact_name = 'Fodor Csaba';
            $team_3->contact_email = 'a@aa.com';
            $team_3->leadership_presentation = '-';
            $team_3->description = '-';
            $team_3->juridical_person_name = 'Fodor Csaba';
            $team_3->juridical_person_address = 'Abcde';
            $team_3->juridical_person_tax_number = '01234';
            $team_3->juridical_person_bank_account = '01234';
            $team_3->save();

            $team_4 = Team::firstOrNew([
                'name' => 'Élthes Alajos',
                'district_id' => $district_2->id,
            ]);
            $team_4->team_number = '152';
            $team_4->address = 'Abcde';
            $team_4->foundation_date = '2000-06-18';
            $team_4->phone = '01234';
            $team_4->email = 'a@aa.com';
            $team_4->contact_name = 'Lázár Annamária';
            $team_4->contact_email = 'a@aa.com';
            $team_4->leadership_presentation = '-';
            $team_4->description = '-';
            $team_4->juridical_person_name = 'Lázár Annamária';
            $team_4->juridical_person_address = 'Abcde';
            $team_4->juridical_person_tax_number = '01234';
            $team_4->juridical_person_bank_account = '01234';
            $team_4->save();
        }

        if (isset($district_3)) {
            $team_5 = Team::firstOrNew([
                'name' => 'Hollósy Simon',
                'district_id' => $district_3->id,
            ]);
            $team_5->team_number = '146';
            $team_5->address = 'Abcde';
            $team_5->foundation_date = '2000-06-18';
            $team_5->phone = '01234';
            $team_5->email = 'a@aa.com';
            $team_5->contact_name = 'Keresztes Annamária';
            $team_5->contact_email = 'a@aa.com';
            $team_5->leadership_presentation = '-';
            $team_5->description = '-';
            $team_5->juridical_person_name = 'Keresztes Annamária';
            $team_5->juridical_person_address = 'Abcde';
            $team_5->juridical_person_tax_number = '01234';
            $team_5->juridical_person_bank_account = '01234';
            $team_5->save();
        }

        if (isset($district_4)) {
            $team_6 = Team::firstOrNew([
                'name' => 'Szent György',
                'district_id' => $district_4->id,
            ]);
            $team_6->team_number = '40';
            $team_6->address = 'Abcde';
            $team_6->foundation_date = '2000-06-18';
            $team_6->phone = '01234';
            $team_6->email = 'a@aa.com';
            $team_6->contact_name = 'Szabó Lajos';
            $team_6->contact_email = 'a@aa.com';
            $team_6->leadership_presentation = '-';
            $team_6->description = '-';
            $team_6->juridical_person_name = 'Szabó Lajos';
            $team_6->juridical_person_address = 'Abcde';
            $team_6->juridical_person_tax_number = '01234';
            $team_6->juridical_person_bank_account = '01234';
            $team_6->save();

            $team_7 = Team::firstOrNew([
                'name' => 'Nagyboldogasszony',
                'district_id' => $district_4->id,
            ]);
            $team_7->team_number = '141';
            $team_7->address = 'Abcde';
            $team_7->foundation_date = '2000-06-18';
            $team_7->phone = '01234';
            $team_7->email = 'a@aa.com';
            $team_7->contact_name = 'Illyés Botond';
            $team_7->contact_email = 'a@aa.com';
            $team_7->leadership_presentation = '-';
            $team_7->description = '-';
            $team_7->juridical_person_name = 'Illyés Botond';
            $team_7->juridical_person_address = 'Abcde';
            $team_7->juridical_person_tax_number = '01234';
            $team_7->juridical_person_bank_account = '01234';
            $team_7->save();
        }

        // troops
        if (isset($team_6)) {
            $troop_1 = Troop::firstOrNew([
                'name' => 'Madarak',
                'team_id' => $team_6->id,
            ]);
            $troop_1->troop_leader_name = 'Anton';
            $troop_1->troop_leader_phone = '01234';
            $troop_1->troop_leader_email = 'a@aa.com';
            $troop_1->save();

            $troop_2 = Troop::firstOrNew([
                'name' => 'Virágok',
                'team_id' => $team_6->id,
            ]);
            $troop_2->troop_leader_name = 'Attila';
            $troop_2->troop_leader_phone = '01234';
            $troop_2->troop_leader_email = 'a@aa.com';
            $troop_2->save();
        }

        if (isset($team_7)) {
            $troop_3 = Troop::firstOrNew([
                'name' => 'Madarak',
                'team_id' => $team_7->id,
            ]);
            $troop_3->troop_leader_name = 'Edina';
            $troop_3->troop_leader_phone = '01234';
            $troop_3->troop_leader_email = 'a@aa.com';
            $troop_3->save();

            $troop_4 = Troop::firstOrNew([
                'name' => 'Virágok',
                'team_id' => $team_7->id,
            ]);
            $troop_4->troop_leader_name = 'Eszter';
            $troop_4->troop_leader_phone = '01234';
            $troop_4->troop_leader_email = 'a@aa.com';
            $troop_4->save();

            $troop_5 = Troop::firstOrNew([
                'name' => 'Halak',
                'team_id' => $team_7->id,
            ]);
            $troop_5->troop_leader_name = 'Erika';
            $troop_5->troop_leader_phone = '01234';
            $troop_5->troop_leader_email = 'a@aa.com';
            $troop_5->save();
        }

        // patrols
        if (isset($team_6)) {
            $patrol_1 = Patrol::firstOrNew([
                'name' => 'Sasok',
                'team_id' => $team_6->id,
            ]);
            if (isset($troop_1)) {
                $patrol_1->troop_id = $troop_1->id;
            }
            $patrol_1->patrol_leader_name = 'Szabi';
            $patrol_1->patrol_leader_phone = '01234';
            $patrol_1->patrol_leader_email = 'a@aa.com';
            $patrol_1->age_group = '2000-2004';
            $patrol_1->save();
            
            $patrol_2 = Patrol::firstOrNew([
                'name' => 'Pulykák',
                'team_id' => $team_6->id,
            ]);
            if (isset($troop_1)) {
                $patrol_2->troop_id = $troop_1->id;
            }
            $patrol_2->patrol_leader_name = 'Péter';
            $patrol_2->patrol_leader_phone = '01234';
            $patrol_2->patrol_leader_email = 'a@aa.com';
            $patrol_2->age_group = '2005-2009';
            $patrol_2->save();
            
            $patrol_3 = Patrol::firstOrNew([
                'name' => 'Farkasok',
                'team_id' => $team_6->id,
            ]);
            $patrol_3->patrol_leader_name = 'Ferenc';
            $patrol_3->patrol_leader_phone = '01234';
            $patrol_3->patrol_leader_email = 'a@aa.com';
            $patrol_3->age_group = '1995-1999';
            $patrol_3->save();
        }

        if (isset($team_7)) {
            $patrol_4 = Patrol::firstOrNew([
                'name' => 'Fácánok',
                'team_id' => $team_7->id,
            ]);
            if (isset($troop_3)) {
                $patrol_4->troop_id = $troop_3->id;
            }
            $patrol_4->patrol_leader_name = 'Fruzsina';
            $patrol_4->patrol_leader_phone = '01234';
            $patrol_4->patrol_leader_email = 'a@aa.com';
            $patrol_4->age_group = '2000-2004';
            $patrol_4->save();
            
            $patrol_5 = Patrol::firstOrNew([
                'name' => 'Verebek',
                'team_id' => $team_7->id,
            ]);
            if (isset($troop_3)) {
                $patrol_5->troop_id = $troop_3->id;
            }
            $patrol_5->patrol_leader_name = 'Verónika';
            $patrol_5->patrol_leader_phone = '01234';
            $patrol_5->patrol_leader_email = 'a@aa.com';
            $patrol_5->age_group = '2005-2009';
            $patrol_5->save();
            
            $patrol_6 = Patrol::firstOrNew([
                'name' => 'Zergék',
                'team_id' => $team_7->id,
            ]);
            $patrol_6->patrol_leader_name = 'Zoltán';
            $patrol_6->patrol_leader_phone = '01234';
            $patrol_6->patrol_leader_email = 'a@aa.com';
            $patrol_6->age_group = '1995-1999';
            $patrol_6->save();
            
            $patrol_7 = Patrol::firstOrNew([
                'name' => 'Orchideák',
                'team_id' => $team_7->id,
            ]);
            if (isset($troop_4)) {
                $patrol_7->troop_id = $troop_4->id;
            }
            $patrol_7->patrol_leader_name = 'Zsuzsa';
            $patrol_7->patrol_leader_phone = '01234';
            $patrol_7->patrol_leader_email = 'a@aa.com';
            $patrol_7->age_group = '2005-2009';
            $patrol_7->save();
        }
    }
}
