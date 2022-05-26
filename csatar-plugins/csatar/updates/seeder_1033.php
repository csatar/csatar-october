<?php namespace Csatar\Csatar\Updates;

use Seeder;
use Csatar\Csatar\Models\Allergy;
use Csatar\Csatar\Models\Association;
use Csatar\Csatar\Models\ChronicIllness;
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

class Seeder1033 extends Seeder
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
        
        foreach($allergies as $title) {
            $allergies = Allergy::create([
                'name' => $title
            ]);
        }

        // legal relationships
        $legalRelationships = [
            'Alakuló csapat tag',
            'Újonc',
            'Tag',
            'Tiszteletbeli tag'
        ];
        
        for($i = 0; $i < count($legalRelationships); ++$i) {
            $legalRelationship = LegalRelationship::create([
                'name' => $legalRelationships[$i],
                'sort_order' => $i + 1
            ]);
        }

        // special tests
        $special_tests = [
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
        
        foreach($special_tests as $name) {
            $special_test = SpecialTest::create([
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
        
        foreach($specialDiets as $title) {
            $specialDiets = SpecialDiet::create([
                'name' => $title
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
        
        foreach($religions as $title) {
            $religions = Religion::create([
                'name' => $title
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
        
        foreach($tshirtSizes as $title) {
            $tshirtSizes = TShirtSize::create([
                'name' => $title
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
        
        foreach($chronicIllnesses as $title) {
            $chronicIllness = ChronicIllness::create([
                'name' => $title
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
        for($i = 0; $i < count($hierarchyItems); ++$i) {
            $hierachyItem = Hierarchy::create([
                'name' => $hierarchyItems[$i],
                'parent_id' => $idOfLastElement,
                'sort_order' => $i + 1
            ]);
            $idOfLastElement = $hierachyItem->id;
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
        
        foreach($associations as $name) {
            $association = Association::create([
                'name' => $name,
                'contact_name' => 'Abcde',
                'contact_email' => 'ab@ab.ab',
                'address' => 'Abcde',
                'leadership_presentation' => 'A',
            ]);
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
            $foodSensitivity = FoodSensitivity::create([
                'name' => $name
            ]);
        }

        // promises
        $promises = [
            'Kiscserkész igéret',
            'Cserkész fogadalom',
        ];
        
        foreach($promises as $name) {
            $promise = Promise::create([
                'name' => $name
            ]);
        }

        // professional qualifications
        $professional_qualifications = [
            'Regős',
        ];
        
        foreach($professional_qualifications as $name) {
            $professional_qualification = ProfessionalQualification::create([
                'name' => $name
            ]);
        }

        // leadership qualifications
        $leadership_qualifications = [
            'Segédőrsvezető képzés',
            'Őrsvezető képzés',
            'Felnőtt őrsvezető képzés',
            'Segédvezető képzés',
            'Cserkész vezető',
       ];
       
       foreach($leadership_qualifications as $name) {
           $leadership_qualification = LeadershipQualification::create([
               'name' => $name
           ]);
       }
    }
}