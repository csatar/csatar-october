<?php namespace Csatar\Csatar\Updates;

use Seeder;
use Csatar\Csatar\Models\Allergy;
use Csatar\Csatar\Models\Association;
use Csatar\Csatar\Models\ChronicIllness;
use Csatar\Csatar\Models\Currency;
use Csatar\Csatar\Models\FoodSensitivity;
use Csatar\Csatar\Models\Hierarchy;
use Csatar\Csatar\Models\LeadershipQualification;
use Csatar\Csatar\Models\LegalRelationship;
use Csatar\Csatar\Models\ProfessionalQualification;
use Csatar\Csatar\Models\Promise;
use Csatar\Csatar\Models\Religion;
use Csatar\Csatar\Models\SpecialDiet;
use Csatar\Csatar\Models\SpecialTest;
use Csatar\Csatar\Models\TShirtSize;
use Csatar\Forms\Models\Form;

class SeederData extends Seeder
{
    public function run()
    {
        // allergies
        $allergies = [
            'Ételintollerancia',
            'Ételallergiák',
            'Pollen alergia/Szénanátha',
            'Belélegzései allergia',
            'Rovarméreg allergia',
            'Kontakt allergia (vegyszerekre, anyagokra)',
            'Gyógyszerallergia',
            'Egyéb'
        ];
        
        foreach($allergies as $name) {
            $allergy = Allergy::firstOrCreate([
                'name' => $name
            ]);
        }

        // legal relationships
        $legalRelationships = [
            'Alakuló csapat tag',
            'Újonc',
            'Tag',
            'Tiszteletbeli tag'
        ];
        
        foreach($legalRelationships as $name) {
            $legalRelationship = LegalRelationship::firstOrCreate([
                'name' => $name
            ]);
        }

        // special tests
        $specialTests = [
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
        
        foreach($specialTests as $name) {
            $specialTest = SpecialTest::firstOrCreate([
                'name' => $name
            ]);
        }

        // special diets
        $specialDiets = [
            'Sporttáplálkozás',
            'Gluténmentes',
            'Szénhidrátmentes',
            'Laktózmentes',
            'Paleo',
            'Vegán',
            'Vegetáriánus'
        ];
        
        foreach($specialDiets as $name) {
            $specialDiet = SpecialDiet::firstOrCreate([
                'name' => $name
            ]);
        }
        
        // religions
        $religions = [
            'Adventista',
            'Baptista',
            'Evangélikus',
            'Görög katolikus',
            'Jehova tanúi',
            'Muzulmán',
            'Ortodox',
            'Református',
            'Római katolikus',
            'Unitárius',
            'Más felekezethez tartozó'
        ];
        
        foreach($religions as $name) {
            $religion = Religion::firstOrCreate([
                'name' => $name
            ]);
        }
        
        // t-shirt sizes
        $tshirtSizes = [
            '4',
            '6',
            '8',
            '10',
            '12',
            'XS',
            'S',
            'M',
            'L',
            'XL',
            'XXL',
            '3XL',
            '4XL',
            '5XL'
        ];
        
        foreach($tshirtSizes as $name) {
            $tshirtSize = TShirtSize::firstOrCreate([
                'name' => $name
            ]);
        }
        
        // chronic illnesses
        $chronicIllnesses = [
            'Magas vérnyomás',
            'Szívelégtelenség',
            'Allergia',
            'Cukorbetegség',
            'Mozgásszervi betegségek',
            'Pajzsmirigy működési zavar',
            'Schizophrenia, schizotypiás és paranoid zavarok',
            'Daganatos betegség',
            'Krónikus légzési elégtelenség',
            'Veseelégtelenség',
            'HIV/SIDA'
        ];
        
        foreach($chronicIllnesses as $name) {
            $chronicIllness = ChronicIllness::firstOrCreate([
                'name' => $name
            ]);
        }

        // hierarchy
        $hierarchyItems = [
            'RMCSSZ',
            'Körzetvezető',
            'Csapatvezető',
            'Rajvezető',
            'Őrsvezető',
            'Cserkész',
        ];
        
        $idOfLastElement = null;
        foreach($hierarchyItems as $name) {
            $hierachyItem = Hierarchy::firstOrNew([
                'name' => $name,
            ]);
            $hierachyItem->parent_id = $idOfLastElement;
            $hierachyItem->save();

            $idOfLastElement = $hierachyItem->id;
        }

        // currencies
        $currencies = [
            'EUR',
            'HRK',
            'HUF',
            'RON',
            'RSD',
            'UAH',
        ];
        
        foreach($currencies as $code) {
            $currency = Currency::firstOrCreate([
                'code' => $code
            ]);
        }

        // associations
        $associations = [
            'Horvátországi magyar cserkészek',
            'Kárpátaljai Magyar Cserkészszövetség',
            'Külföldi Magyar Cserkészszövetség',
            'Magyar Cserkészszövetség',
            'Romániai Magyar Cserkészszövetség',
            'Szlovákiai Magyar Cserkészszövetség',
            'Vajdasági Magyar Cserkészszövetség',
        ];
        
        $legalRelationship1 = LegalRelationship::where('name', 'Alakuló csapat tag')->first();
        $legalRelationship2 = LegalRelationship::where('name', 'Tag')->first();

        foreach($associations as $name) {
            $association = Association::firstOrNew([
                'name' => $name,
            ]);
            $association->contact_name = 'Abcde';
            $association->contact_email = 'ab@ab.ab';
            $association->address = 'Abcde';
            $association->leadership_presentation = 'A';
            switch ($name) {
                case 'Horvátországi magyar cserkészek':
                    $association->ecset_code_suffix = 'H';
                    $association->currency_id = Currency::where('code', 'HRK')->first()->id;
                    $association->team_fee = 0;
                    break;
                case 'Kárpátaljai Magyar Cserkészszövetség':
                    $association->currency_id = Currency::where('code', 'UAH')->first()->id;
                    $association->team_fee = 0;
                    $association->ecset_code_suffix = 'KÁ';
                    break;
                case 'Külföldi Magyar Cserkészszövetség':
                    $association->ecset_code_suffix = 'KÜ';
                    $association->currency_id = Currency::where('code', 'EUR')->first()->id;
                    $association->team_fee = 0;
                    break;
                case 'Magyar Cserkészszövetség':
                    $association->ecset_code_suffix = 'M';
                    $association->currency_id = Currency::where('code', 'HUF')->first()->id;
                    $association->team_fee = 0;
                    break;
                case 'Romániai Magyar Cserkészszövetség':
                    $association->ecset_code_suffix = 'E';
                    $association->currency_id = Currency::where('code', 'RON')->first()->id;
                    $association->team_fee = 300;
                    break;
                case 'Szlovákiai Magyar Cserkészszövetség':
                    $association->ecset_code_suffix = 'F';
                    $association->currency_id = Currency::where('code', 'EUR')->first()->id;
                    $association->team_fee = 0;
                    break;
                case 'Vajdasági Magyar Cserkészszövetség':
                    $association->ecset_code_suffix = 'D';
                    $association->currency_id = Currency::where('code', 'RSD')->first()->id;
                    $association->team_fee = 0;
                    break;
                default:
                    break;
            }
            
            // associations - legal relationships pivot
            if ($association->legal_relationships->where('id', $legalRelationship1->id)->first() == null) {
                $association->legal_relationships()->attach($legalRelationship1, ['membership_fee' => 0]);
            }
            if ($association->legal_relationships->where('id', $legalRelationship2->id)->first() == null) {
                $association->legal_relationships()->attach($legalRelationship2, ['membership_fee' => 0]);
            }

            $association->save();
        }

        // food sensitivities
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
            $foodSensitivity = FoodSensitivity::firstOrCreate([
                'name' => $name
            ]);
        }

        // promises
        $promises = [
            'Kiscserkész igéret',
            'Cserkész fogadalom',
        ];
        
        foreach($promises as $name) {
            $promise = Promise::firstOrCreate([
                'name' => $name
            ]);
        }

        // professional qualifications
        $professionalQualifications = [
            'Regős',
        ];
        
        foreach($professionalQualifications as $name) {
            $professionalQualification = ProfessionalQualification::firstOrCreate([
                'name' => $name
            ]);
        }

        // leadership qualifications
        $leadershipQualifications = [
            'Segédőrsvezető képzés',
            'Őrsvezető képzés',
            'Felnőtt őrsvezető képzés',
            'Segédvezető képzés',
            'Cserkész vezető',
       ];
       
        foreach($leadershipQualifications as $name) {
            $leadershipQualification = LeadershipQualification::firstOrCreate([
                'name' => $name
            ]);
        }

        // seeders for the Forms plugin
        $forms = Form::all();
        foreach($forms as $form) {
            $form->slugAttributes();
            $form->save();
        }

        $scout = Form::firstOrCreate([
            'title' => 'Tag',
            'model' => 'Csatar\Csatar\Models\Scout',
        ]);
        $association = Form::firstOrCreate([
            'title' => 'Szövetség',
            'model' => 'Csatar\Csatar\Models\Association',
        ]);
        $district = Form::firstOrCreate([
            'title' => 'Körzet',
            'model' => 'Csatar\Csatar\Models\District',
        ]);
        $team = Form::firstOrCreate([
            'title' => 'Csapat',
            'model' => 'Csatar\Csatar\Models\Team',
        ]);
        $team = Form::firstOrCreate([
            'title' => 'Raj',
            'model' => 'Csatar\Csatar\Models\Troop',
        ]);
        $team = Form::firstOrCreate([
            'title' => 'Őrs',
            'model' => 'Csatar\Csatar\Models\Patrol',
        ]);
        $team = Form::firstOrCreate([
            'title' => 'Csapatjelentés',
            'model' => 'Csatar\Csatar\Models\TeamReport',
        ]);
    }
}
