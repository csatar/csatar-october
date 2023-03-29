<?php namespace Csatar\Csatar\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Csatar\Csatar\Classes\Enums\Gender;
use Csatar\Csatar\Models\Allergy;
use Csatar\Csatar\Models\ChronicIllness;
use Csatar\Csatar\Models\District;
use Csatar\Csatar\Models\Association;
use Csatar\Csatar\Models\AgeGroup;
use Csatar\Csatar\Classes\Enums\Status;
use Csatar\Csatar\Models\FoodSensitivity;
use Csatar\Csatar\Models\LeadershipQualification;
use Csatar\Csatar\Models\Mandate;
use Csatar\Csatar\Models\MandateType;
use Csatar\Csatar\Models\MembershipCard;
use Csatar\Csatar\Models\Patrol;
use Csatar\Csatar\Models\Promise;
use Csatar\Csatar\Models\Scout;
use Csatar\Csatar\Models\SpecialTest;
use Csatar\Csatar\Models\Team;
use Csatar\Csatar\Models\Test;
use Csatar\Csatar\Models\Training;
use Csatar\Csatar\Models\TrainingQualification;
use Csatar\Csatar\Models\Troop;
use Csatar\Csatar\Models\LegalRelationship;
use Csatar\Csatar\Models\Religion;
use Input;
use System\Models\File;
use October\Rain\Filesystem\Filesystem;
use Log;
use October\Rain\Filesystem\Zip;

class JsonImport extends Controller
{
    private $association;
    private $associationId;
    private $statusMap;
    private $countyMap;
    private $genderMap;
    private $ageGroupMap;

    public $requiredPermissions = [
        'csatar.admin'
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Csatar.Csatar', 'main-menu-item-seeder-data', 'side-menu-item-json-import');
        $this->statusMap = [
            'a' => Status::ACTIVE,
            'i' => Status::INACTIVE,
            't' => Status::INACTIVE,
        ];

        $this->association = Association::where('name', 'Romániai Magyar Cserkészszövetség')->first();
        $this->associationId = $this->association->id;

        $this->countyMap = [
            "Arad"             => "Arad",
            "Argyas"           => "Argeș",
            "Bákó"             => "Bacău",
            "Beszterce-Naszód" => "Bistrița-Năsăud",
            "Bihar"            => "Bihor",
            "Buzó"             => "Buzău",
            "Brassó"           => "Brașov",
            "Fehér"            => "Alba",
            "Galac"            => "Galați",
            "Hargita"          => "Harghita",
            "Hunyad"           => "Hunedoara",
            "Kolozs"           => "Cluj",
            "Kostanca"         => "Constanța",
            "Kovászna"         => "Covasna",
            "Krassó-Szörény"   => "Caraș-Severin",
            "Máramaros"        => "Maramureș",
            "Maros"            => "Mureș",
            "Szatmár"          => "Satu Mare",
            "Szeben"           => "Sibiu",
            "Szilágy"          => "Sălaj",
            "Temes"            => "Timiș",
            "Vráncsa"          => "Vrancea",
        ];

        $this->genderMap = [
            'F' => Gender::MALE,
            'L' => Gender::FEMALE,
            'V' => Gender::MIXED,
        ];

        $this->ageGroupMap = [
            'fkoly' => AgeGroup::where('association_id', $this->associationId)->where('name', 'Farkaskölyök')->first()->id ?? 0,
            'kcs'   => AgeGroup::where('association_id', $this->associationId)->where('name', 'Kiscserkész')->first()->id ?? 0,
            'cs'    => AgeGroup::where('association_id', $this->associationId)->where('name', 'Cserkész')->first()->id ?? 0,
            'f'     => AgeGroup::where('association_id', $this->associationId)->where('name', 'Felfedező')->first()->id ?? 0,
            'k'     => AgeGroup::where('association_id', $this->associationId)->where('name', 'Vándor')->first()->id ?? 0,
            'v'     => AgeGroup::where('association_id', $this->associationId)->where('name', 'Vegyes')->first()->id ?? 0,
        ];

    }

    private array $legalRelationshipMap = [];
    private array $religionMap = [];
    private array $allergiesMap = [];
    private array $chronicIllnesMap = [];
    private array $foodSensitivitiesMap = [];

    private function prepareScoutRelatedMappings() {
        $this->legalRelationshipMap = [
            'a' => LegalRelationship::firstOrCreate(['name' => 'Alakuló csapat tag'])->id,
            'ujonc' => LegalRelationship::firstOrCreate(['name' => 'Újonc'])->id,
            'tag' => LegalRelationship::firstOrCreate(['name' => 'Tag'])->id,
            'ttag' => LegalRelationship::firstOrCreate(['name' => 'Tiszteletbeli tag'])->id,
            'ervenytelen' => LegalRelationship::firstOrCreate(['name' => 'Érvénytelen adat'])->id,
        ];

        $this->religionMap = [
            'adv'  => Religion::where('name', 'Adventista')->first()->id,
            'bapt' => Religion::where('name', 'Baptista')->first()->id,
            'evan' => Religion::where('name', 'Evangélikus')->first()->id,
            'gkat' => Religion::where('name', 'Görög katolikus')->first()->id,
            'jeh'  => Religion::where('name', 'Jehova tanúi')->first()->id,
            'kat'  => Religion::where('name', 'Római katolikus')->first()->id,
            'mas'  => Religion::where('name', 'Más felekezethez tartozó')->first()->id,
            'muz'  => Religion::where('name', 'Muzulmán')->first()->id,
            'ort'  => Religion::where('name', 'Ortodox')->first()->id,
            'ref'  => Religion::where('name', 'Református')->first()->id,
            'unit' => Religion::where('name', 'Unitárius')->first()->id,
        ];

        $allergies = Allergy::all();
        $this->allergiesMap = [
            "Nincs" => $allergies->where('name', 'Nincs')->first(),
            "Darázscsípés" => $allergies->where('name', 'Rovarméreg allergia')->first(),
            "Por, pollen, macskaszőr, árpa" => $allergies->where('name', 'Egyéb')->first(),
            "az idiótákra" => $allergies->where('name', 'Nincs')->first(),
            "por" => $allergies->where('name', 'Egyéb')->first(),
            "-" => $allergies->where('name', 'Nincs')->first(),
            "házi por, őszi mezei füvek, dió" => $allergies->where('name', 'Egyéb')->first(),
            "Brufen, szalicil/ szalicilsav" => $allergies->where('name', 'Egyéb')->first(),
            "Pollen" => $allergies->where('name', 'Pollen alergia/Szénanátha')->first(),
            "Tartósító szerek" => $allergies->where('name', 'Egyéb')->first(),
            "Enyhe allergia rovarcsípésekre" => $allergies->where('name', 'Rovarméreg allergia')->first(),
            "poratka, toll" => $allergies->where('name', 'Egyéb')->first(),
            "Házi por, macska, széna, porzós növények, állatok" => $allergies->where('name', 'Egyéb')->first(),
            "Penicilin érzékenység" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "Furazonido, Biseptol" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "paracetamolra" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "meh csipes" => $allergies->where('name', 'Rovarméreg allergia')->first(),
            "Gyapjú" => $allergies->where('name', 'Egyéb')->first(),
            "Parlagfű" => $allergies->where('name', 'Pollen alergia/Szénanátha')->first(),
            "metoclopramid" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "fű és por" => $allergies->where('name', 'Egyéb')->first(),
            "Citrom, citromsav, citromsó. (margarin, üzleti keksz, süti, torta, üditő, fagyi, cukorka, felvágott, virsli, ketchup. Piros kiütés a bőrön, nehezen múlik)" => $allergies->where('name', 'Egyéb')->first(),
            "Codein,Nurofen" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "Penicillin érzékenység" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "poratka, házipor" => $allergies->where('name', 'Egyéb')->first(),
            "ampicilin" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "por, pollen" => $allergies->where('name', 'Egyéb')->first(),
            "poratka" => $allergies->where('name', 'Egyéb')->first(),
            "parlagfű, pázsitfű" => $allergies->where('name', 'Pollen alergia/Szénanátha')->first(),
            "a kórházi sebtapaszra" => $allergies->where('name', 'Egyéb')->first(),
            "Amoxicilin" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "Pollen és penészgomba" => $allergies->where('name', 'Egyéb')->first(),
            "penész, por, kazein" => $allergies->where('name', 'Egyéb')->first(),
            "astm –bronsic alergic" => $allergies->where('name', 'Egyéb')->first(),
            "Tejfehérje allergia, intolerancia" => $allergies->where('name', 'Ételintollerancia')->first(),
            "pollen" => $allergies->where('name', 'Pollen alergia/Szénanátha')->first(),
            "gyapjú, pollenek" => $allergies->where('name', 'Egyéb')->first(),
            "tejérzékeny" => $allergies->where('name', 'Ételintollerancia')->first(),
            "Algocalmin" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "Pollen, kutya - és macskaszőr" => $allergies->where('name', 'Egyéb')->first(),
            "méhcsipés" => $allergies->where('name', 'Rovarméreg allergia')->first(),
            "kutyaszőr, por és poratka" => $allergies->where('name', 'Egyéb')->first(),
            "Rovarcsípés" => $allergies->where('name', 'Rovarméreg allergia')->first(),
            "darázscsípés" => $allergies->where('name', 'Rovarméreg allergia')->first(),
            "lázcsillapító" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "Nincs." => $allergies->where('name', 'Nincs')->first(),
            "Méhcsípés" => $allergies->where('name', 'Rovarméreg allergia')->first(),
            "levendula" => $allergies->where('name', 'Egyéb')->first(),
            "penicillin" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "ibuprofen" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "Bronsitis, Aszma" => $allergies->where('name', 'Egyéb')->first(),
            "poratka, pollenallergia" => $allergies->where('name', 'Egyéb')->first(),
            "nap" => $allergies->where('name', 'Egyéb')->first(),
            "tej" => $allergies->where('name', 'Ételintollerancia')->first(),
            "Por" => $allergies->where('name', 'Egyéb')->first(),
            "Ospen, Rovarcsipések" => $allergies->where('name', 'Egyéb')->first(),
            "Penicilin" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "Van, de nem tudja mi váltja ki" => $allergies->where('name', 'Egyéb')->first(),
            "hársfa pollen" => $allergies->where('name', 'Pollen alergia/Szénanátha')->first(),
            "Por, Pollen, Penesz" => $allergies->where('name', 'Egyéb')->first(),
            "por, gluten erzekeny" => $allergies->where('name', 'Egyéb')->first(),
            "gluten" => $allergies->where('name', 'Ételintollerancia')->first(),
            "Kakaó" => $allergies->where('name', 'Ételintollerancia')->first(),
            "Antibiotukim" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "Virágporok, gabonaporok" => $allergies->where('name', 'Egyéb')->first(),
            "nincs" => $allergies->where('name', 'Nincs')->first(),
            "Eurespal" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "Paracetamol" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "szénanátha" => $allergies->where('name', 'Pollen alergia/Szénanátha')->first(),
            "házi por, pázsitfű" => $allergies->where('name', 'Egyéb')->first(),
            "Poratka" => $allergies->where('name', 'Egyéb')->first(),
            "Eper" => $allergies->where('name', 'Ételintollerancia')->first(),
            "Biseptor" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "Porallergia" => $allergies->where('name', 'Egyéb')->first(),
            "enyhe tejallergia (nagyobb mennyiségnél)" => $allergies->where('name', 'Ételintollerancia')->first(),
            "Hányingercsillapító" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "Cefalexin gyógyszere" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "Ízesített joghurt" => $allergies->where('name', 'Ételintollerancia')->first(),
            "széna, házipor atka" => $allergies->where('name', 'Egyéb')->first(),
            "Csípések (pók, szúnyog, méh, darázs)" => $allergies->where('name', 'Rovarméreg allergia')->first(),
            "por, penész, macskaszőr" => $allergies->where('name', 'Egyéb')->first(),
            "hal" => $allergies->where('name', 'Ételintollerancia')->first(),
            "Augmentin, Debridat" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "Kutyaszőr" => $allergies->where('name', 'Egyéb')->first(),
            "Szója" => $allergies->where('name', 'Ételintollerancia')->first(),
            "Rovar csipés" => $allergies->where('name', 'Rovarméreg allergia')->first(),
            "Penicilin családra" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "Ibuprofen" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "májkrém" => $allergies->where('name', 'Ételintollerancia')->first(),
            "eritromicin" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "Virágpor, szúnyogcsípés (kezelés alatt)" => $allergies->where('name', 'Egyéb')->first(),
            "darázs csipés" => $allergies->where('name', 'Rovarméreg allergia')->first(),
            "NINCS" => $allergies->where('name', 'Nincs')->first(),
            "macska és házipor" => $allergies->where('name', 'Egyéb')->first(),
            "Por. tol, állat ször, penész" => $allergies->where('name', 'Egyéb')->first(),
            "Preduiszon/predniszon gyógyszerek" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "nap :)" => $allergies->where('name', 'Egyéb')->first(),
            "napsütés, epa, paradicsom" => $allergies->where('name', 'Egyéb')->first(),
            "Szúnyogcsípés, macskaszőr" => $allergies->where('name', 'Egyéb')->first(),
            "Nurofen, Brufen" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "dió" => $allergies->where('name', 'Ételintollerancia')->first(),
            "Élelmiszerpenész" => $allergies->where('name', 'Ételintollerancia')->first(),
            "I tipusu cukorbeteg" => $allergies->where('name', 'Egyéb')->first(),
            "csokolade, kakao, tej" => $allergies->where('name', 'Ételintollerancia')->first(),
            "Porotka, Penicillin, tollú" => $allergies->where('name', 'Egyéb')->first(),
            "Por és atka allergia" => $allergies->where('name', 'Egyéb')->first(),
            "Méhecske csípés" => $allergies->where('name', 'Rovarméreg allergia')->first(),
            "kontakt dermatitisz - bizonyos növényekre a természetből" => $allergies->where('name', 'Egyéb')->first(),
            "Gyógyszer: cefalosporin, novosept, zinnat" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "SUMETROLIM" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "rovarcsípés" => $allergies->where('name', 'Rovarméreg allergia')->first(),
            "Gyógyszer" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "Penicilin és száemazékai" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "zinat" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "napallergia" => $allergies->where('name', 'Egyéb')->first(),
            "Gumicukor" => $allergies->where('name', 'Ételintollerancia')->first(),
            "Por, akta, penész" => $allergies->where('name', 'Egyéb')->first(),
            "dió, hárs pollen" => $allergies->where('name', 'Egyéb')->first(),
            "por, pollen, amoxacilin" => $allergies->where('name', 'Egyéb')->first(),
            "???" => $allergies->where('name', 'Nincs')->first(),
            "szeder" => $allergies->where('name', 'Ételintollerancia')->first(),
            "Sumatrolin" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "macskaszőr, poratkák" => $allergies->where('name', 'Egyéb')->first(),
            "laktóz" => $allergies->where('name', 'Ételintollerancia')->first(),
            "porallergia, tünetmentes" => $allergies->where('name', 'Egyéb')->first(),
            "por allergia" => $allergies->where('name', 'Egyéb')->first(),
            "darázscsípésre" => $allergies->where('name', 'Rovarméreg allergia')->first(),
            "méhcsípés" => $allergies->where('name', 'Rovarméreg allergia')->first(),
            "méh csípés" => $allergies->where('name', 'Rovarméreg allergia')->first(),
            "Dió, Paradicsom, Vinete" => $allergies->where('name', 'Ételintollerancia')->first(),
            "Vancomicin gyógyszer" => $allergies->where('name', 'Gyógyszerallergia')->first(),
            "időszakos ekcéma" => $allergies->where('name', 'Egyéb')->first(),
        ];

        $chronicIllnes = ChronicIllness::all();
        $this->chronicIllnesMap = [
            "NINCS" => $chronicIllnes->where('name', 'Nincs krónikus betegsége')->first(),
            "komolytalanság?!" => $chronicIllnes->where('name', 'Nincs krónikus betegsége')->first(),
            "cukorbetegség" => $chronicIllnes->where('name', 'Cukorbetegség')->first(),
            "-" => $chronicIllnes->where('name', 'Nincs krónikus betegsége')->first(),
            "I tipusú diabétesz, hipertónia" => $chronicIllnes->where('name', 'Egyéb')->first(),
            "Bokasüllyedés, lúdtalp" => $chronicIllnes->where('name', 'Mozgásszervi betegségek')->first(),
            "magas vérnyomás" => $chronicIllnes->where('name', 'Magas vérnyomás')->first(),
            "asztma" => $chronicIllnes->where('name', 'Krónikus légzési elégtelenség')->first(),
            "pajzsmirigy gyulladás" => $chronicIllnes->where('name', 'Pajzsmirigy működési zavar')->first(),
            "Cukorbeteg" => $chronicIllnes->where('name', 'Cukorbetegség')->first(),
            "magas vérnyomásra hajlamos" => $chronicIllnes->where('name', 'Magas vérnyomás')->first(),
            "Magas vérnyomás" => $chronicIllnes->where('name', 'Magas vérnyomás')->first(),
            "hypothyreosis" => $chronicIllnes->where('name', 'Egyéb')->first(),
            "cukorbaj" => $chronicIllnes->where('name', 'Cukorbetegség')->first(),
            "Allergia, asztma" => $chronicIllnes->where('name', 'Egyéb')->first(),
            "Asztma" => $chronicIllnes->where('name', 'Krónikus légzési elégtelenség')->first(),
            "Kataplexia  Narkolepszia szindróma" => $chronicIllnes->where('name', 'Egyéb')->first(),
            "magas got. szint (máj)" => $chronicIllnes->where('name', 'Egyéb')->first(),
            "tüdő TBC" => $chronicIllnes->where('name', 'Egyéb')->first(),
            "gerincferdülés" => $chronicIllnes->where('name', 'Mozgásszervi betegségek')->first(),
            "Nincsenek." => $chronicIllnes->where('name', 'Nincs krónikus betegsége')->first(),
            "Diabétesz" => $chronicIllnes->where('name', 'Egyéb')->first(),
            "penicilin érzékeny" => $chronicIllnes->where('name', 'Egyéb')->first(),
            "Forgókopás" => $chronicIllnes->where('name', 'Mozgásszervi betegségek')->first(),
            "trombocitoremia" => $chronicIllnes->where('name', 'Egyéb')->first(),
            "nincs" => $chronicIllnes->where('name', 'Nincs krónikus betegsége')->first(),
            "Veleszületett szívrendellenesség (RO: malformatie la inima)" => $chronicIllnes->where('name', 'Szívelégtelenség')->first(),
            "szivzorej" => $chronicIllnes->where('name', 'Szívelégtelenség')->first(),
            "Agyhalál néha, vagy mindig 🤔" => $chronicIllnes->where('name', 'Nincs krónikus betegsége')->first(),
            "Asztma (nem súlyos)" => $chronicIllnes->where('name', 'Krónikus légzési elégtelenség')->first(),
            "cukorbetegség, inzulinfüggő" => $chronicIllnes->where('name', 'Cukorbetegség')->first(),
            "ADHD" => $chronicIllnes->where('name', 'Egyéb')->first(),
            "Astm bronsic" => $chronicIllnes->where('name', 'Krónikus légzési elégtelenség')->first(),
            "1 tipusú diabéttesz, inzulinfüggő" => $chronicIllnes->where('name', 'Cukorbetegség')->first(),
            "epilepszia" => $chronicIllnes->where('name', 'Egyéb')->first(),
            "Crigler Najjar" => $chronicIllnes->where('name', 'Egyéb')->first(),
            "Miopia, Lombaris Diszkropatia" => $chronicIllnes->where('name', 'Egyéb')->first(),
            "magasabb vércukorszínt" => $chronicIllnes->where('name', 'Cukorbetegség')->first(),
            "Nincs" => $chronicIllnes->where('name', 'Nincs krónikus betegsége')->first(),
            "enyhe hörgőasztma" => $chronicIllnes->where('name', 'Krónikus légzési elégtelenség')->first(),
            "Asztma, szív elégtelenség" => $chronicIllnes->where('name', 'Egyéb')->first(),
            "atópiás dermatitisz" => $chronicIllnes->where('name', 'Egyéb')->first(),
            "Astma Bronsic" => $chronicIllnes->where('name', 'Krónikus légzési elégtelenség')->first(),
            "Marshall szindróma" => $chronicIllnes->where('name', 'Egyéb')->first(),
            "Pitvari Septum Defectus (ASD), Atópiás asztma, Allergiás Rhinitis" => $chronicIllnes->where('name', 'Egyéb')->first(),
        ];

        $foodSensitivities = FoodSensitivity::all();
        $this->foodSensitivitiesMap = [
            'az én ételem nem érzékeny' => null,
            'Vegán' => $foodSensitivities->where('name', 'Egyéb')->first(),
            '-' => null,
            'Tartósító szerek' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'tejfehérje' => $foodSensitivities->where('name', 'tejfehérje (kazein)')->first(),
            'disznóhúsra' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'lakóz érzékeny' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'tej' => $foodSensitivities->where('name', 'tejfehérje (kazein)')->first(),
            'glutén érzékeny' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'Tejfehérje-érzékenység, nem fogyaszthat semmiféle tejszármazékot' => $foodSensitivities->where('name', 'tejfehérje (kazein)')->first(),
            'Édes tejen kívűl nem eszik meg semmit "ami fehér".' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'sajtfélék' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'Nincs.' => null,
            'tojás' => $foodSensitivities->where('name', 'tojás')->first(),
            'élelmiszer-adalékanyag intolerancia' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'csoki' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'Tojás - enyhe' => $foodSensitivities->where('name', 'tojás')->first(),
            'Tejfehérje' => $foodSensitivities->where('name', 'tejfehérje (kazein)')->first(),
            'üditő, méz, édességek' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'Glutén érzékenység' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'Eper' => $foodSensitivities->where('name', 'eper')->first(),
            'Laktóz' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'Tej' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'magvas' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'Ízesített joghurt' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'hal és bármilyen halas étel' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'Laktóz érzékeny' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'máj' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'NINCS' => null,
            'Laktóz, glutén' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'csokoládé, kakaó' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'Nincs' => null,
            'Laktózérzékenység' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'Liszt érzékenység (glutén intolerancia)' => $foodSensitivities->where('name', 'liszt')->first(),
            'ételszínezék' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'eper' => $foodSensitivities->where('name', 'eper')->first(),
            'laktóz' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'aprómagvas gyümölcsök' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'tejtermék' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'Búzafehérje intolerancia' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'nincs' => null,
            'aszalt barack' => $foodSensitivities->where('name', 'Egyéb')->first(),
            'narancs, kiwi' => $foodSensitivities->where('name', 'Ételintollerancia')->first(),
            'laktózra, izfozokra, szinezekre' => $foodSensitivities->where('name', 'Egyéb')->first(),
        ];
    }

    public function organizations() {
    }

    public function scouts() {
    }

    public function mandates() {
    }

    public function promises() {
    }

    public function registrationforms() {
    }

    public function membershipcards() {
    }

    public function onUploadAndProcessOrganizations() {
        $file = Input::file('json_file');
        if ($file->isValid()) {
            $file = $file->move(temp_path(), $file->getClientOriginalName());
            $jsonDecoded = json_decode(file_get_contents($file->getRealPath()));
            $data = collect($jsonDecoded);
        }

        $data = $data->groupBy('model');

        //import districts
        foreach ($data['szervezet.korzet'] as $org) {
            $fields = $org->fields;

            $address = $fields->cim_utca ? ($fields->cim_utca . ', ') : '';
            $address .= $fields->cim_irsz ? ($fields->cim_irsz . ', ') : '';
            $address .= $fields->cim_telepules ? ($fields->cim_telepules . ', ') : '';
            $address .= $fields->cim_megye ? (($this->countyMap[$fields->cim_megye] ?? $fields->cim_megye) . ', ') : '';
            $address .= (($fields->cim_orszag == 'Románia'
                    || $fields->cim_orszag == 'Romania'
                    || $fields->cim_orszag == 'romania') ? 'România' : $fields->cim_orszag);

            $district = District::firstOrNew (
                [
                    'association_id'    => $this->associationId,
                    'slug'              => $fields->composed_slug,
                ]
            );

            $district->name          = $fields->nev;
            $district->status        = $this->statusMap[$fields->statusz] ?? null;
            $district->address       = $address;
            $district->description   = $fields->leiras;
            $district->email         = $fields->email;
            $district->website       = $fields->web;
            $district->facebook_page = $fields->facebook;
            $district->bank_account  = $fields->bankszamla_szam;

            $district->ignoreValidation = true;
            $district->forceSave();

            if (!empty($fields->kep)) {
                $path = '/storage/app/media/importedimages/' . $fields->kep;
                $url = url('/') . $path;
                if ((new Filesystem())->existsInsensitive(base_path() . $path)) {
                    $file = new File;
                    $file->fromUrl($url);
                    $district->logo()->add($file);
                } else {
                    Log::warning("Can't attach file $url.");
                }
            }

            $district->ignoreValidation = true;
            $district->forceSave();

            $address = null;
            $district = null;
        }

        //import teams
        foreach ($data['szervezet.csapat'] as $org) {
            $fields = $org->fields;
            if (empty($fields->korzet)) {
                Log::warning("Can not import: $org->model - name: $fields->nev - composed-slug: $fields->composed_slug; 'korzet' is empty");
                continue;
            }
            $district_id = District::where('slug', $fields->korzet)->first()->id ?? null;

            if (empty($district_id)) {
                Log::warning("Can not import: $org->model - name: $fields->nev - composed-slug: $fields->composed_slug; Can't find district: $fields->korzet");
                continue;
            }

            $address = $fields->cim_utca ? ($fields->cim_utca . ', ') : '';
            $address .= $fields->cim_irsz ? ($fields->cim_irsz . ', ') : '';
            $address .= $fields->cim_telepules ? ($fields->cim_telepules . ', ') : '';
            $address .= $fields->cim_megye ? (($this->countyMap[$fields->cim_megye] ?? $fields->cim_megye) . ', ') : '';
            $address .= (($fields->cim_orszag == 'Románia'
                || $fields->cim_orszag == 'Romania'
                || $fields->cim_orszag == 'romania') ? 'România' : $fields->cim_orszag);

            $team = Team::firstOrNew (
                [
                    'team_number'       => $fields->szam,
                ]
            );

            if (!empty($team->district_id) && $team->district->association_id != $this->associationId) {
                $team = new Team();
                $team->team_number = $fields->szam;
            }

            $team->district_id                    = $district_id;
            $team->slug                           = $fields->composed_slug;
            $team->name                           = $fields->nev;
            $team->status                         = $this->statusMap[$fields->statusz] ?? null;
            $team->address                        = $address;
            $team->description                    = $fields->leiras;
            $team->email                          = $fields->email;
            $team->website                        = $fields->web;
            $team->facebook_page                  = $fields->facebook;
            $team->juridical_person_bank_account  = $fields->bankszamla_szam;

            $team->ignoreValidation = true;
            $team->forceSave();

            if (!empty($fields->kep)) {
                $path = '/storage/app/media/importedimages/' . $fields->kep;
                $url = url('/') . $path;
                if ((new Filesystem())->existsInsensitive(base_path() . $path)) {
                    $file = new File;
                    $file->fromUrl($url);
                    $team->logo()->add($file);
                } else {
                    Log::warning("Can't attach file $url.");
                }
            }

            $team->ignoreValidation = true;
            $team->forceSave();

            $address = null;
            $district_id = null;
            $team = null;
        }

        //import troops
        foreach ($data['szervezet.raj'] as $org) {
            $fields = $org->fields;
            if (empty($fields->csapat)) {
                Log::warning("Can not import: $org->model - name: $fields->nev - composed-slug: $fields->composed_slug; 'csapat' is empty");
                continue;
            }
            $team_id = Team::where('slug', $fields->csapat)->first()->id ?? null;

            if (empty($team_id)) {
                Log::warning("Can not import: $org->model - name: $fields->nev - composed-slug: $fields->composed_slug; Can't find team: $fields->csapat");
                continue;
            }

            $troop = Troop::firstOrNew (
                [
                    'team_id'           => $team_id,
                    'slug'              => $fields->composed_slug,
                ]
            );

            $troop->name                           = $fields->nev;
            $troop->status                         = $this->statusMap[$fields->statusz] ?? null;
            $troop->email                          = $fields->email;
            $troop->website                        = $fields->web;
            $troop->facebook_page                  = $fields->facebook;

            $troop->ignoreValidation = true;
            $troop->forceSave();

            if (!empty($fields->kep)) {
                $path = '/storage/app/media/importedimages/' . $fields->kep;
                $url = url('/') . $path;
                if ((new Filesystem())->existsInsensitive(base_path() . $path)) {
                    $file = new File;
                    $file->fromUrl($url);
                    $troop->logo()->add($file);
                } else {
                    Log::warning("Can't attach file $url.");
                }
            }

            $troop->ignoreValidation = true;
            $troop->forceSave();

            $troop = null;
            $team_id = null;
        }

        //import patrols
        foreach ($data['szervezet.ors'] as $org) {
            $fields = $org->fields;
            if (empty($fields->csapat) && empty($fields->raj)) {
                Log::warning("Can not import: $org->model - name: $fields->nev - composed-slug: $fields->composed_slug; 'csapat' and 'raj' is empty");
                continue;
            }
            $troop = Troop::where('slug', $fields->raj)->first() ?? null;
            $team_id  = Team::where('slug', $fields->csapat)->first()->id ?? $troop->team_id;
            if (empty($team_id) && empty($troop->id)) {
                Log::warning("Can not import: $org->model - name: $fields->nev - composed-slug: $fields->composed_slug; Can't find team: $fields->csapat and troop: $fields->raj");
                continue;
            }

            $patrol = Patrol::firstOrNew (
                [
                    'team_id'           => $team_id,
                    'troop_id'          => $troop->id ?? null,
                    'slug'              => $fields->composed_slug,
                ]
            );

            $patrol->name                           = $fields->nev;
            $patrol->status                         = $this->statusMap[$fields->statusz] ?? null;
            $patrol->gender                         = $this->genderMap[$fields->nem] ?? '';
            $patrol->email                          = $fields->email;
            $patrol->website                        = $fields->web;
            $patrol->facebook_page                  = $fields->facebook;

            $patrol->age_group_id                   = isset($fields->korosztaly[0]) && isset($this->ageGroupMap[$fields->korosztaly[0]]) ? $this->ageGroupMap[$fields->korosztaly[0]] : $this->ageGroupMap['v'];

            $patrol->ignoreValidation = true;
            $patrol->forceSave();

            if (!empty($fields->kep)) {
                $path = '/storage/app/media/importedimages/' . $fields->kep;
                $url = url('/') . $path;
                if ((new Filesystem())->existsInsensitive(base_path() . $path)) {
                    $file = new File;
                    $file->fromUrl($url);
                    $patrol->logo()->add($file);
                } else {
                    Log::warning("Can't attach file $url.");
                }

            }

            $patrol->ignoreValidation = true;
            $patrol->forceSave();

            $troop = null;
            $patrol = null;
            $team_id  = null;
        }

    }

    public function onUploadAndProcessScouts() {
        $scoutsFile = Input::file('scouts_json_file');
        $pivotFile = Input::file('pivot_json_file');

        if ($scoutsFile->isValid()) {
            $scoutsFile = $scoutsFile->move(temp_path(), $scoutsFile->getClientOriginalName());
            $scoutsData = collect(json_decode(file_get_contents($scoutsFile->getRealPath())));
        }

        if ($pivotFile->isValid()) {
            $pivotFile = $pivotFile->move(temp_path(), $pivotFile->getClientOriginalName());
            $pivotData = collect(json_decode(file_get_contents($pivotFile->getRealPath())));
        }

        if (empty($scoutsData) || empty($pivotData)) {
            return;
        }

        $pivotData = $pivotData->mapWithKeys(function ($item) {
            return [$item->fields->tag[0] => $item->fields->ors[0]];
        });

        $this->prepareScoutRelatedMappings();

        foreach ($scoutsData as $scout) {

            $fields = $scout->fields; //dd($fields);

            if (empty($fields->csapat)) {
                Log::warning("Can not import scout: $fields->ecsk name: $fields->nev $fields->keresztnev; 'csapat' is empty");
                continue;
            }
            $team_id = Team::where('slug', $fields->csapat)->first()->id ?? null;

            if (empty($team_id)) {
                Log::warning("Can not import scout: $fields->ecsk name: $fields->nev $fields->keresztnev; Can't find team: " . $fields->csapat[0]);
                continue;
            }

            if (!empty($fields->ecsk) && isset($pivotData[$fields->ecsk])) {
                $patrol_slug = $pivotData[$fields->ecsk];
                $patrol = Patrol::where('team_id', $team_id)->where('slug', $patrol_slug)->first();
            }

            if (!empty($patrol->troop_id)) {
                $troop = Troop::find($patrol->troop_id);
            }

            $scout = Scout::withTrashed()->firstOrNew (
                [
                    'ecset_code'        => $fields->ecsk, //TODO check
                    'team_id'           => $team_id,
                ]
            );

            $scout->troop_id                       = $troop->id ?? null;
            $scout->patrol_id                      = $patrol->id ?? null;
            $scout->name_prefix                    = $fields->nev_elotag ?? null;
            $scout->family_name                    = $fields->nev ?? null;
            $scout->given_name                     = $fields->keresztnev ?? null;
            $scout->nickname                       = $fields->becenev ?? null;
            $scout->email                          = $fields->email ?? null;
            $scout->phone                          = $fields->telefonszam ?? null;
            $scout->personal_identification_number = !empty($fields->jellemzok->id_12) ? substr($fields->jellemzok->id_12, 0, 20) : null;
            $scout->gender                         = $this->genderMap[$fields->nem] ?? null;
            $scout->is_active                      = $this->statusMap[$fields->statusz] ?? null;
            $scout->legal_relationship_id          = isset($fields->jogviszony[0]) ? ($this->legalRelationshipMap[$fields->jogviszony[0]] ?? $this->legalRelationshipMap['ervenytelen']) : $this->legalRelationshipMap['ervenytelen'];
            $scout->religion_id                    = isset($fields->felekezet[0]) ? ($this->religionMap[$fields->felekezet[0]] ?? $this->religionMap['mas']) : $this->religionMap['mas'];
            $scout->nationality                    = $fields->jellemzok->id_13 ?? null;
            $scout->birthdate                      = $fields->szuletesi_datum ?? null;
            $scout->maiden_name                    = $fields->szuletesi_nev ?? null;
            $scout->birthplace                     = $fields->szuletesi_hely ?? null;
            $scout->address_country                = $fields->cim_orszag ?? null;
            $scout->address_zipcode                = $fields->cim_irsz ?? null;
            $scout->address_county                 = $fields->cim_megye ?? null;
            $scout->address_location               = $fields->cim_telepules ?? null;
            $scout->address_street                 = $fields->cim_utcahsz ?? null;
            $scout->mothers_name                   = $fields->anyja_neve ?? $fields->anya_nev ?? null;
            $scout->mothers_phone                  = $fields->anya_telefon ?? null;
            $scout->mothers_email                  = $fields->anya_email ?? null;
            $scout->fathers_name                   = $fields->apa_nev ?? null;
            $scout->fathers_phone                  = $fields->apa_telefon ?? null;
            $scout->fathers_email                  = $fields->apa_email ?? null;
            $scout->elementary_school              = $fields->jellemzok->id_25 ?? null;
            $scout->primary_school                 = $fields->jellemzok->id_26 ?? null;
            $scout->secondary_school               = $fields->jellemzok->id_27 ?? null;
            $scout->post_secondary_school          = $fields->jellemzok->id_28 ?? null;
            $scout->college                        = $fields->jellemzok->id_29 ?? null;
            $scout->university                     = $fields->jellemzok->id_30 ?? null;
            $scout->foreign_language_knowledge     = $fields->jellemzok->id_14 ?? null;
            $scout->occupation                     = $fields->jellemzok->id_31 ?? null;
            $scout->workplace                      = $fields->jellemzok->id_32 ?? null;
            $scout->comment                        = $fields->jellemzok->id_18 ?? null;
            $scout->raw_import                     = $fields;

            if ($fields->statusz === 't') {
                $scout->deleted_at = date('Y-m-d');
            }

            $scout->ignoreValidation = true;
            $scout->forceSave();

            //allergies
            if (!empty($fields->jellemzok->id_19) && !empty($this->allergiesMap[$fields->jellemzok->id_19])) {
                $allergy = $this->allergiesMap[$fields->jellemzok->id_19];
                if (!$scout->allergies->contains($allergy)) {
                    $scout->allergies()->attach(
                        $this->allergiesMap[$fields->jellemzok->id_19],
                        ['comment' => $fields->jellemzok->id_19]
                    );
                }
            }

            //chronic_illnesses
            if (!empty($fields->jellemzok->id_17) && !empty($this->chronicIllnesMap[$fields->jellemzok->id_17])) {
                $chronic_illnesses = $this->chronicIllnesMap[$fields->jellemzok->id_17];
                if (!$scout->chronic_illnesses->contains($chronic_illnesses)) {
                    $scout->chronic_illnesses()->attach(
                        $this->chronicIllnesMap[$fields->jellemzok->id_17],
                        ['comment' => $fields->jellemzok->id_17]
                    );
                }
            }

            //foodsensitivites
            if (!empty($fields->jellemzok->id_20) && !empty($this->foodSensitivitiesMap[$fields->jellemzok->id_20])) {
                $foodSensitivity = $this->foodSensitivitiesMap[$fields->jellemzok->id_20];
                if (!$scout->food_sensitivities->contains($foodSensitivity)) {
                    $scout->food_sensitivities()->attach(
                        $this->foodSensitivitiesMap[$fields->jellemzok->id_20],
                        ['comment' => $fields->jellemzok->id_20]
                    );
                }
            }

            if (!empty($fields->kep)) {
                $path = '/storage/app/media/importedimages/' . $fields->kep;
                $lastDotPosition = strrchr($path, ".");
                $extension = substr($lastDotPosition, 1);
                $path = str_replace($extension, strtolower($extension), $path);
                $url = url('/') . $path;
                if ((new Filesystem())->existsInsensitive(base_path() . $path)) {
                    $file = new File;
                    $file->fromUrl($url);
                    $scout->profile_image()->add($file);
                } else {
                    Log::warning("Can't attach file $url.");
                }
            }

            $team_id = null;
            $troop = null;
            $patrol = null;
            $patrol_slug = null;
            $scout = null;
        }
    }

    public function onUploadAndProcessMandates() {
        $mandateTypesFile = Input::file('mandate_types_json_file');
        $pivotFile = Input::file('pivot_json_file');

        if ($mandateTypesFile->isValid()) {
            $mandateTypesFile = $mandateTypesFile->move(temp_path(), $mandateTypesFile->getClientOriginalName());
            $mandateTypesData = collect(json_decode(file_get_contents($mandateTypesFile->getRealPath())));
        }

        if ($pivotFile->isValid()) {
            $pivotFile = $pivotFile->move(temp_path(), $pivotFile->getClientOriginalName());
            $pivotData = collect(json_decode(file_get_contents($pivotFile->getRealPath())));
        }

        if (empty($mandateTypesData) || empty($pivotData)) {
            return;
        }

        $orgTypeModelNameMap = [
            'szov' => '\Csatar\Csatar\Models\Association',
            'vk' => '\Csatar\Csatar\Models\Association',
            'korz' => '\Csatar\Csatar\Models\District',
            'csap' => '\Csatar\Csatar\Models\Team',
            'raj' => '\Csatar\Csatar\Models\Troop',
            'ors' => '\Csatar\Csatar\Models\Patrol',
        ];

        $districtsMap = District::all();
        $districtsMap = $districtsMap->mapWithKeys(function ($item) {
            return [
                $item->slug => $item
            ];
        });

        $teamsMap = Team::all();
        $teamsMap = $teamsMap->mapWithKeys(function ($item) {
            return [
                $item->slug => $item
            ];
        });

        $troopsMap = Troop::all();
        $troopsMap = $troopsMap->mapWithKeys(function ($item) {
            return [
                $item->slug => $item
            ];
        });

        $patrolsMap = Patrol::all();
        $patrolsMap = $patrolsMap->mapWithKeys(function ($item) {
            return [
                $item->slug => $item
            ];
        });

        $organizationsArraysMap = [
            '\Csatar\Csatar\Models\Association' => [ 'rmcssz' => $this->association],
            '\Csatar\Csatar\Models\District' => $districtsMap,
            '\Csatar\Csatar\Models\Team' => $teamsMap,
            '\Csatar\Csatar\Models\Troop' => $troopsMap,
            '\Csatar\Csatar\Models\Patrol' => $patrolsMap,
        ];

        $scoutsMap = Scout::withTrashed()->get();
        $scoutsMap = $scoutsMap->mapWithKeys(function ($item) {
            return [
                $item->ecset_code => $item->id
            ];
        });

        $mandateTypesPreMapped = $mandateTypesData->mapWithKeys(function ($item) use ($orgTypeModelNameMap) {
            $first = mb_substr($item->nev, 0, 1, "utf8");
            $rest = mb_substr($item->nev, 1, null, "utf8");
            $name = mb_strtoupper($first, "utf8") . $rest;
            return [
                $item->rovidites => [
                    'name' => $name,
                    'association_id' => $this->associationId,
                    'organization_type_model_name' => $orgTypeModelNameMap[$item->szint],
                    'overlap_allowed' => $item->overlap,
                    'is_vk' => $item->szint == 'vk' ? 1 : 0,
                    'rovidities' => $item->rovidites,
                ]
            ];
        });

        $mandateTypesMap = $mandateTypesPreMapped->mapWithKeys(function ($item) {
            $mandateType = MandateType::firstOrNew([
                'name' => $item['name'],
                'association_id' => $this->associationId,
                'organization_type_model_name' => $item['organization_type_model_name'],
            ]);

            $mandateType->overlap_allowed = $mandate->overlap_allowed ?? $item['overlap_allowed'];
            $mandateType->is_vk = $item['is_vk'];
            $mandateType->save();

            return [
                $item['rovidities'] => $mandateType
            ];
        });

        foreach ($pivotData as $item) {
            $data = $item->fields;
            $mandateType = $mandateTypesMap->get($data->megbizatas[1]);

            $organizationMap = $organizationsArraysMap[$mandateType->organization_type_model_name];
            $model = $organizationMap[$mandateType->is_vk ? 'rmcssz' : $data->egyseg[0]];
            $mandate = Mandate::firstOrNew([
                'scout_id' => $scoutsMap[$data->tag[0]],
                'mandate_type_id' => $mandateType->id,
                'mandate_model_id' => $model->id,
                'mandate_model_type' => $mandateType->organization_type_model_name,
                'start_date' => $data->kezdete ?? $data->vege ?? date('Y-m-d'),
                'end_date' => $data->vege ?? null,
            ]);

            $mandate->comment = $data->tovabbi_nev;
            $mandate->ignoreValidation = true;
            $mandate->save();

            $mandateType = null;
            $organizationMap = null;
            $model = null;
            $mandate = null;
        }
    }

    public function onUploadAndProcessPromisesAndQualifications() {
        $pivotFile = Input::file('pivot_json_file');

        if ($pivotFile->isValid()) {
            $pivotFile = $pivotFile->move(temp_path(), $pivotFile->getClientOriginalName());
            $pivotData = collect(json_decode(file_get_contents($pivotFile->getRealPath())));
        }

        if (empty($pivotData)) {
            return;
        }

        $totalItemsToAdd = $pivotData->count();
        $pivotData = $pivotData->groupBy('fields.tag');

        $promisesMap = [
            "cserkeszfogadalom" => 'Cserkész fogadalom',
            "kiscserkesz-igeret" => 'Kiscserkész igéret',
            "felnottcserkesz-fogadalom" => 'Felnőttcserkész fogadalom',
            "segedorsvezetoi-fogadalom" => "Segédőrsvezetői fogadalom",
            "orsvezetoi-fogadalom" => "Őrsvezetői fogadalom",
            "cserkesztiszti-fogadalom" => "Cserkesztiszti fogadalom",
        ];
        $promisesMap = array_map(function($value) {
            return Promise::firstOrCreate([ 'name' => $value ]);
        }, $promisesMap);

        $testsMap = [
            "ujoncproba" => "Újonc próba",
            "elso-proba" => "Első próba",
            "masodik-proba" => "Második próba",
            "harmadik-proba" => "Harmadik próba",
            "piros-pajzs-proba" => "Piros pajzs próba",
            "feher-pajzs-proba" => "Fehér pajzs próba",
            "zold-pajzs-proba" => "Zöld pajzs próba"
        ];
        $testsMap = array_map(function($value) {
            return Test::firstOrCreate([ 'name' => $value ]);
        }, $testsMap);

        $specialTestsMap = [
            "szakacs" => "Szakács",
            "tuzrako" => "Tűzrakó",
            "harom-sastoll" => "Három sastoll",
            "elsosegely" => "Elsősegély",
            "tuzolto" => "Tűzoltó",
            "egereszolyv" => "Egerészölyv",
            "egyhazszolgalat" => "Egyházszolgálat",
            "olvaso" => "Olvasó",
            "penzugyi" => "Pénzügyi",
        ];
        $specialTestsMap = array_map(function($value) {
            return SpecialTest::firstOrCreate([ 'name' => $value ]);
        }, $specialTestsMap);

        $leadershipQualificationsMap = [
            "segedorsvezeto" => "Segédőrsvezető képzés",
            "orsvezeto" => "Őrsvezető képzés", //kivéve ahol a tovabbi_adatok.kepzes tartalmazza az "FŐVK"-t. ott a "Felnőtt őrsvezető képzés"-t kell hozzárendelni
            "fovk-orsvezeto" => "Felnőtt őrsvezető képzés",
            "segedtiszt" => "Segédvezető képzés",
            "cserkesztiszt" => "Cserkész vezető",
        ];
        $leadershipQualificationsMap = array_map(function($value) {
            return LeadershipQualification::firstOrCreate([ 'name' => $value ]);
        }, $leadershipQualificationsMap);

        $trainingQualificationsMap = [
            "mameluk" => "Mameluk",
            "orsvezeto-kikepzo" => "ŐVK kiképző",
            "segedorsvezeto-kikepzo" => "SÖV kiképző",
            "segedtiszt-kikepzo" => "ST kiképző",
        ];
        $trainingQualificationsMap = array_map(function($value) {
            return TrainingQualification::firstOrCreate([ 'name' => $value ]);
        }, $trainingQualificationsMap);

        $typeMap = [
            "cserkeszfogadalom" => [ 'promises', $promisesMap],
            "kiscserkesz-igeret" => [ 'promises', $promisesMap],
            "felnottcserkesz-fogadalom" => [ 'promises', $promisesMap],
            "ujoncproba" => [ 'tests', $testsMap],
            "elso-proba" => [ 'tests', $testsMap],
            "masodik-proba" => [ 'tests', $testsMap],
            "harmadik-proba" => [ 'tests', $testsMap],
            "piros-pajzs-proba" => [ 'tests', $testsMap],
            "feher-pajzs-proba" => [ 'tests', $testsMap],
            "zold-pajzs-proba" => [ 'tests', $testsMap],
            "szakacs" => [ 'special_tests', $specialTestsMap],
            "tuzrako" => [ 'special_tests', $specialTestsMap],
            "harom-sastoll" => [ 'special_tests', $specialTestsMap],
            "elsosegely" => [ 'special_tests', $specialTestsMap],
            "tuzolto" => [ 'special_tests', $specialTestsMap],
            "egereszolyv" => [ 'special_tests', $specialTestsMap],
            "egyhazszolgalat" => [ 'special_tests', $specialTestsMap],
            "olvaso" => [ 'special_tests', $specialTestsMap],
            "penzugyi" => [ 'special_tests', $specialTestsMap],
            "segedorsvezeto" => [ 'leadership_qualifications', $leadershipQualificationsMap],
            "segedorsvezetoi-fogadalom" => [ 'promises', $promisesMap],
            "orsvezeto" => [ 'leadership_qualifications', $leadershipQualificationsMap],
            "orsvezetoi-fogadalom" => [ 'promises', $promisesMap],
            "fovk-orsvezeto" => [ 'leadership_qualifications', $leadershipQualificationsMap],
            "segedtiszt" => [ 'leadership_qualifications', $leadershipQualificationsMap],
            "cserkesztiszt" => [ 'leadership_qualifications', $leadershipQualificationsMap],
            "cserkesztiszti-fogadalom" => [ 'promises', $promisesMap],
            "mameluk" => [ 'training_qualifications', $trainingQualificationsMap],
            "orsvezeto-kikepzo" => [ 'training_qualifications', $trainingQualificationsMap],
            "segedorsvezeto-kikepzo" => [ 'training_qualifications', $trainingQualificationsMap],
            "segedtiszt-kikepzo" => [ 'training_qualifications', $trainingQualificationsMap],
        ];
        $scoutsMap = Scout::withTrashed()->get();
        $scoutsMap = $scoutsMap->mapWithKeys(function ($item) {
            return [
                $item->ecset_code => $item
            ];
        });

        $totalItemsAdded = 0;

        foreach ($pivotData as $scoutCode => $groupedData) {
            $itemsToAdd = count($groupedData);
            $itemsAdded = 0;

            $scout = $scoutsMap[$scoutCode];

            if (empty($scout)) {
                continue;
            }

            foreach ($groupedData as $data) {
                $data = $data->fields;

                if (!isset($data->tag[0]) || empty($data->datum) || empty($data->tovabbi_adatok)) {
                    Log::warning("Could not add record, data incomplete: " . serialize($data));
                    continue;
                }

                $kepesites = $data->kepesites[0];
                if (!empty($data->tovabbi_adatok->kepzes) && strpos($data->tovabbi_adatok->kepzes, 'FŐVK') !== false) {
                    $kepesites = 'fovk-' . $kepesites;
                }
                $relationName = $typeMap[$kepesites][0];
                $relationModel = $typeMap[$kepesites][1][$kepesites];
                $pivotArray = [
                    'date' => $data->datum ?? null,
                    'location' => $data->tovabbi_adatok->helyszin ?? null,
                ];
                if (array_key_exists($kepesites, $trainingQualificationsMap) || array_key_exists($kepesites, $leadershipQualificationsMap)) {

                    if (empty($data->tovabbi_adatok->betetlap)
                        || empty($data->tovabbi_adatok->kv)
                        || empty($data->tovabbi_adatok->kepzes)
                    ) {
                        Log::warning("Could not add record, data incomplete: " . serialize($data));
                        continue;
                    }

                    if (!empty($data->tovabbi_adatok->kepzes)) {
                        $training = Training::firstOrCreate([
                            'name' => $data->tovabbi_adatok->kepzes
                        ]);
                    }

                    $pivotArray = array_merge($pivotArray, [
                        'qualification_certificate_number' => $data->tovabbi_adatok->betetlap ?? null,
                        'training_id' => $training->id ?? null,
                        'training_name' => $training->name ?? null,
                        'qualification_leader' => $data->tovabbi_adatok->kv ?? null,
                    ]);
                }

                if (!$scout->{$relationName}->contains($relationModel)) {
                    $scout->{$relationName}()->add($relationModel,$pivotArray);
                    $itemsAdded++;
                }

                $training = null;
                $pivotArray = null;
                $kepesites = null;
                $relationName = null;
                $relationModel = null;
            }

            if ($itemsToAdd > $itemsAdded) {
                Log::warning("Could add only $itemsAdded of $itemsToAdd promises and qualifications to scout: $scoutCode;");
            }

            $totalItemsAdded += $itemsAdded;

            $scout = null;
        }

        Log::warning("Could add $totalItemsAdded of $totalItemsToAdd promises and qualifications");
    }

    public function onProcessRegistrationForms() {
        $zip = Input::file('registration_files_zip');

        if (empty($zip) || $zip->getClientOriginalExtension() != 'zip') {
            return;
        }

        $files = $this->unzip($zip);

        foreach ($files as $key => $path) {
            if (is_dir($path)) {
                continue;
            }

            $file = new File;
            $file->data = $path;
            $file->save();
            $originalName = $file->getFileName();
            $postfixWithExtension = 'E.' . $file->getExtension();
            if (strpos($originalName, $postfixWithExtension)) {
                $ecset_code = str_replace($postfixWithExtension, '-E', $originalName);
                $scout = Scout::withTrashed()->where('ecset_code', $ecset_code)->first();

                if (empty($scout)) {
                    Log::warning("Could not find scout with identifier: $ecset_code. Registration from not imported.");
                    unlink($path);
                    continue;
                }

                $scout->registration_form()->add($file);
                $scout->ignoreValidation = true;
                $scout->forceSave();

                unlink($path);
            }
        }
    }

    public function onProcessMemberCardsData() {
        $dataFile = Input::file('member_cards_data_file');

        if ($dataFile->isValid()) {
            $dataFile = $dataFile->move(temp_path(), $dataFile->getClientOriginalName());
            $memberCardsData = collect(json_decode(file_get_contents($dataFile->getRealPath())));
        }

        foreach ($memberCardsData as $memberCardData) {
            $data = $memberCardData->fields;
            $ecset_code = $data->tag[0];
            $scout = Scout::withTrashed()->where('ecset_code', $ecset_code)->first();

            if (empty($scout)) {
                Log::warning("Could not find scout with identifier: $ecset_code. Member card data from not imported.");
                continue;
            }

            $membershipCard = MembershipCard::firstOrNew(['rfid_tag' => $data->rfid_tag ]);

            $membershipCard->scout_id           = $scout->id;
            $membershipCard->issued_date_time   = $data->legyartva;
            $membershipCard->active             = $data->ervenyes;
            $membershipCard->note               = $data->megjegyzes;

            $membershipCard->forceSave();
        }



    }

    private function stripFileExtension($file)
    {
        return substr($file, 0, strlen($file) - 4);
    }

    private function unzip($file)
    {
        $dir = temp_path() . '/reg_files_zip';

        Zip::extract($file->getRealPath(), $dir);

        $files = array_diff(scandir($dir), array('.', '..'));
        $fileArray = [];
        foreach ($files as $file) {
            $fileArray[$this->stripFileExtension($file)] = $dir . '/' . $file;
        }
        return $fileArray;
    }
}
