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
use Csatar\Csatar\Models\Patrol;
use Csatar\Csatar\Models\Scout;
use Csatar\Csatar\Models\Team;
use Csatar\Csatar\Models\Troop;
use Csatar\Csatar\Models\LegalRelationship;
use Csatar\Csatar\Models\Religion;
use Input;
use System\Models\File;
use October\Rain\Filesystem\Filesystem;
use Log;

class JsonImport extends Controller
{
    private $associationId;
    private $statusMap;
    private $countyMap;
    private $genderMap;
    private $ageGroupMap;

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Csatar.Csatar', 'main-menu-item-seeder-data', 'side-menu-item-json-import');
        $this->statusMap = [
            'a' => Status::ACTIVE,
            'i' => Status::INACTIVE,
        ];

        $this->associationId = (Association::where('name', 'Romániai Magyar Cserkészszövetség')->first())->id;

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
            'a' => LegalRelationship::where('name', 'Alakuló csapat tag')->first()->id,
            'ujonc' => LegalRelationship::where('name', 'Újonc')->first()->id,
            'tag' => LegalRelationship::where('name', 'Tag')->first()->id,
            'ttag' => LegalRelationship::where('name', 'Tiszteletbeli tag')->first()->id,
            'ervenytelen' => LegalRelationship::where('name', 'Érvénytelen adat')->first()->id,
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

        $this->allergiesMap = [
            "Nincs" => Allergy::where('name', 'Nincs')->first(),
            "Darázscsípés" => Allergy::where('name', 'Rovarméreg allergia')->first(),
            "Por, pollen, macskaszőr, árpa" => Allergy::where('name', 'Egyéb')->first(),
            "az idiótákra" => Allergy::where('name', 'Nincs')->first(),
            "por" => Allergy::where('name', 'Egyéb')->first(),
            "-" => Allergy::where('name', 'Nincs')->first(),
            "házi por, őszi mezei füvek, dió" => Allergy::where('name', 'Egyéb')->first(),
            "Brufen, szalicil/ szalicilsav" => Allergy::where('name', 'Egyéb')->first(),
            "Pollen" => Allergy::where('name', 'Pollen alergia/Szénanátha')->first(),
            "Tartósító szerek" => Allergy::where('name', 'Egyéb')->first(),
            "Enyhe allergia rovarcsípésekre" => Allergy::where('name', 'Rovarméreg allergia')->first(),
            "poratka, toll" => Allergy::where('name', 'Egyéb')->first(),
            "Házi por, macska, széna, porzós növények, állatok" => Allergy::where('name', 'Egyéb')->first(),
            "Penicilin érzékenység" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "Furazonido, Biseptol" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "paracetamolra" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "meh csipes" => Allergy::where('name', 'Rovarméreg allergia')->first(),
            "Gyapjú" => Allergy::where('name', 'Egyéb')->first(),
            "Parlagfű" => Allergy::where('name', 'Pollen alergia/Szénanátha')->first(),
            "metoclopramid" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "fű és por" => Allergy::where('name', 'Egyéb')->first(),
            "Citrom, citromsav, citromsó. (margarin, üzleti keksz, süti, torta, üditő, fagyi, cukorka, felvágott, virsli, ketchup. Piros kiütés a bőrön, nehezen múlik)" => Allergy::where('name', 'Egyéb')->first(),
            "Codein,Nurofen" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "Penicillin érzékenység" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "poratka, házipor" => Allergy::where('name', 'Egyéb')->first(),
            "ampicilin" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "por, pollen" => Allergy::where('name', 'Egyéb')->first(),
            "poratka" => Allergy::where('name', 'Egyéb')->first(),
            "parlagfű, pázsitfű" => Allergy::where('name', 'Pollen alergia/Szénanátha')->first(),
            "a kórházi sebtapaszra" => Allergy::where('name', 'Egyéb')->first(),
            "Amoxicilin" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "Pollen és penészgomba" => Allergy::where('name', 'Egyéb')->first(),
            "penész, por, kazein" => Allergy::where('name', 'Egyéb')->first(),
            "astm –bronsic alergic" => Allergy::where('name', 'Egyéb')->first(),
            "Tejfehérje allergia, intolerancia" => Allergy::where('name', 'Ételintollerancia')->first(),
            "pollen" => Allergy::where('name', 'Pollen alergia/Szénanátha')->first(),
            "gyapjú, pollenek" => Allergy::where('name', 'Egyéb')->first(),
            "tejérzékeny" => Allergy::where('name', 'Ételintollerancia')->first(),
            "Algocalmin" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "Pollen, kutya - és macskaszőr" => Allergy::where('name', 'Egyéb')->first(),
            "méhcsipés" => Allergy::where('name', 'Rovarméreg allergia')->first(),
            "kutyaszőr, por és poratka" => Allergy::where('name', 'Egyéb')->first(),
            "Rovarcsípés" => Allergy::where('name', 'Rovarméreg allergia')->first(),
            "darázscsípés" => Allergy::where('name', 'Rovarméreg allergia')->first(),
            "lázcsillapító" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "Nincs." => Allergy::where('name', 'Nincs')->first(),
            "Méhcsípés" => Allergy::where('name', 'Rovarméreg allergia')->first(),
            "levendula" => Allergy::where('name', 'Egyéb')->first(),
            "penicillin" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "ibuprofen" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "Bronsitis, Aszma" => Allergy::where('name', 'Egyéb')->first(),
            "poratka, pollenallergia" => Allergy::where('name', 'Egyéb')->first(),
            "nap" => Allergy::where('name', 'Egyéb')->first(),
            "tej" => Allergy::where('name', 'Ételintollerancia')->first(),
            "Por" => Allergy::where('name', 'Egyéb')->first(),
            "Ospen, Rovarcsipések" => Allergy::where('name', 'Egyéb')->first(),
            "Penicilin" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "Van, de nem tudja mi váltja ki" => Allergy::where('name', 'Egyéb')->first(),
            "hársfa pollen" => Allergy::where('name', 'Pollen alergia/Szénanátha')->first(),
            "Por, Pollen, Penesz" => Allergy::where('name', 'Egyéb')->first(),
            "por, gluten erzekeny" => Allergy::where('name', 'Egyéb')->first(),
            "gluten" => Allergy::where('name', 'Ételintollerancia')->first(),
            "Kakaó" => Allergy::where('name', 'Ételintollerancia')->first(),
            "Antibiotukim" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "Virágporok, gabonaporok" => Allergy::where('name', 'Egyéb')->first(),
            "nincs" => Allergy::where('name', 'Nincs')->first(),
            "Eurespal" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "Paracetamol" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "szénanátha" => Allergy::where('name', 'Pollen alergia/Szénanátha')->first(),
            "házi por, pázsitfű" => Allergy::where('name', 'Egyéb')->first(),
            "Poratka" => Allergy::where('name', 'Egyéb')->first(),
            "Eper" => Allergy::where('name', 'Ételintollerancia')->first(),
            "Biseptor" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "Porallergia" => Allergy::where('name', 'Egyéb')->first(),
            "enyhe tejallergia (nagyobb mennyiségnél)" => Allergy::where('name', 'Ételintollerancia')->first(),
            "Hányingercsillapító" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "Cefalexin gyógyszere" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "Ízesített joghurt" => Allergy::where('name', 'Ételintollerancia')->first(),
            "széna, házipor atka" => Allergy::where('name', 'Egyéb')->first(),
            "Csípések (pók, szúnyog, méh, darázs)" => Allergy::where('name', 'Rovarméreg allergia')->first(),
            "por, penész, macskaszőr" => Allergy::where('name', 'Egyéb')->first(),
            "hal" => Allergy::where('name', 'Ételintollerancia')->first(),
            "Augmentin, Debridat" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "Kutyaszőr" => Allergy::where('name', 'Egyéb')->first(),
            "Szója" => Allergy::where('name', 'Ételintollerancia')->first(),
            "Rovar csipés" => Allergy::where('name', 'Rovarméreg allergia')->first(),
            "Penicilin családra" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "Ibuprofen" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "májkrém" => Allergy::where('name', 'Ételintollerancia')->first(),
            "eritromicin" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "Virágpor, szúnyogcsípés (kezelés alatt)" => Allergy::where('name', 'Egyéb')->first(),
            "darázs csipés" => Allergy::where('name', 'Rovarméreg allergia')->first(),
            "NINCS" => Allergy::where('name', 'Nincs')->first(),
            "macska és házipor" => Allergy::where('name', 'Egyéb')->first(),
            "Por. tol, állat ször, penész" => Allergy::where('name', 'Egyéb')->first(),
            "Preduiszon/predniszon gyógyszerek" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "nap :)" => Allergy::where('name', 'Egyéb')->first(),
            "napsütés, epa, paradicsom" => Allergy::where('name', 'Egyéb')->first(),
            "Szúnyogcsípés, macskaszőr" => Allergy::where('name', 'Egyéb')->first(),
            "Nurofen, Brufen" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "dió" => Allergy::where('name', 'Ételintollerancia')->first(),
            "Élelmiszerpenész" => Allergy::where('name', 'Ételintollerancia')->first(),
            "I tipusu cukorbeteg" => Allergy::where('name', 'Egyéb')->first(),
            "csokolade, kakao, tej" => Allergy::where('name', 'Ételintollerancia')->first(),
            "Porotka, Penicillin, tollú" => Allergy::where('name', 'Egyéb')->first(),
            "Por és atka allergia" => Allergy::where('name', 'Egyéb')->first(),
            "Méhecske csípés" => Allergy::where('name', 'Rovarméreg allergia')->first(),
            "kontakt dermatitisz - bizonyos növényekre a természetből" => Allergy::where('name', 'Egyéb')->first(),
            "Gyógyszer: cefalosporin, novosept, zinnat" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "SUMETROLIM" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "rovarcsípés" => Allergy::where('name', 'Rovarméreg allergia')->first(),
            "Gyógyszer" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "Penicilin és száemazékai" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "zinat" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "napallergia" => Allergy::where('name', 'Egyéb')->first(),
            "Gumicukor" => Allergy::where('name', 'Ételintollerancia')->first(),
            "Por, akta, penész" => Allergy::where('name', 'Egyéb')->first(),
            "dió, hárs pollen" => Allergy::where('name', 'Egyéb')->first(),
            "por, pollen, amoxacilin" => Allergy::where('name', 'Egyéb')->first(),
            "???" => Allergy::where('name', 'Nincs')->first(),
            "szeder" => Allergy::where('name', 'Ételintollerancia')->first(),
            "Sumatrolin" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "macskaszőr, poratkák" => Allergy::where('name', 'Egyéb')->first(),
            "laktóz" => Allergy::where('name', 'Ételintollerancia')->first(),
            "porallergia, tünetmentes" => Allergy::where('name', 'Egyéb')->first(),
            "por allergia" => Allergy::where('name', 'Egyéb')->first(),
            "darázscsípésre" => Allergy::where('name', 'Rovarméreg allergia')->first(),
            "méhcsípés" => Allergy::where('name', 'Rovarméreg allergia')->first(),
            "méh csípés" => Allergy::where('name', 'Rovarméreg allergia')->first(),
            "Dió, Paradicsom, Vinete" => Allergy::where('name', 'Ételintollerancia')->first(),
            "Vancomicin gyógyszer" => Allergy::where('name', 'Gyógyszerallergia')->first(),
            "időszakos ekcéma" => Allergy::where('name', 'Egyéb')->first(),
        ];

        $this->chronicIllnesMap = [
            "NINCS" => ChronicIllness::where('name', 'Nincs krónikus betegsége')->first(),
            "komolytalanság?!" => ChronicIllness::where('name', 'Nincs krónikus betegsége')->first(),
            "cukorbetegség" => ChronicIllness::where('name', 'Cukorbetegség')->first(),
            "-" => ChronicIllness::where('name', 'Nincs krónikus betegsége')->first(),
            "I tipusú diabétesz, hipertónia" => ChronicIllness::where('name', 'Egyéb')->first(),
            "Bokasüllyedés, lúdtalp" => ChronicIllness::where('name', 'Mozgásszervi betegségek')->first(),
            "magas vérnyomás" => ChronicIllness::where('name', 'Magas vérnyomás')->first(),
            "asztma" => ChronicIllness::where('name', 'Krónikus légzési elégtelenség')->first(),
            "pajzsmirigy gyulladás" => ChronicIllness::where('name', 'Pajzsmirigy működési zavar')->first(),
            "Cukorbeteg" => ChronicIllness::where('name', 'Cukorbetegség')->first(),
            "magas vérnyomásra hajlamos" => ChronicIllness::where('name', 'Magas vérnyomás')->first(),
            "Magas vérnyomás" => ChronicIllness::where('name', 'Magas vérnyomás')->first(),
            "hypothyreosis" => ChronicIllness::where('name', 'Egyéb')->first(),
            "cukorbaj" => ChronicIllness::where('name', 'Cukorbetegség')->first(),
            "Allergia, asztma" => ChronicIllness::where('name', 'Egyéb')->first(),
            "Asztma" => ChronicIllness::where('name', 'Krónikus légzési elégtelenség')->first(),
            "Kataplexia  Narkolepszia szindróma" => ChronicIllness::where('name', 'Egyéb')->first(),
            "magas got. szint (máj)" => ChronicIllness::where('name', 'Egyéb')->first(),
            "tüdő TBC" => ChronicIllness::where('name', 'Egyéb')->first(),
            "gerincferdülés" => ChronicIllness::where('name', 'Mozgásszervi betegségek')->first(),
            "Nincsenek." => ChronicIllness::where('name', 'Nincs krónikus betegsége')->first(),
            "Diabétesz" => ChronicIllness::where('name', 'Egyéb')->first(),
            "penicilin érzékeny" => ChronicIllness::where('name', 'Egyéb')->first(),
            "Forgókopás" => ChronicIllness::where('name', 'Mozgásszervi betegségek')->first(),
            "trombocitoremia" => ChronicIllness::where('name', 'Egyéb')->first(),
            "nincs" => ChronicIllness::where('name', 'Nincs krónikus betegsége')->first(),
            "Veleszületett szívrendellenesség (RO: malformatie la inima)" => ChronicIllness::where('name', 'Egyéb')->first(),
            "szivzorej" => ChronicIllness::where('name', 'Egyéb')->first(),
            "Agyhalál néha, vagy mindig 🤔" => ChronicIllness::where('name', 'Nincs krónikus betegsége')->first(),
            "Asztma (nem súlyos)" => ChronicIllness::where('name', 'Krónikus légzési elégtelenség')->first(),
            "cukorbetegség, inzulinfüggő" => ChronicIllness::where('name', 'Cukorbetegség')->first(),
            "ADHD" => ChronicIllness::where('name', 'Egyéb')->first(),
            "Astm bronsic" => ChronicIllness::where('name', 'Krónikus légzési elégtelenség')->first(),
            "1 tipusú diabéttesz, inzulinfüggő" => ChronicIllness::where('name', 'Egyéb')->first(),
            "epilepszia" => ChronicIllness::where('name', 'Egyéb')->first(),
            "Crigler Najjar" => ChronicIllness::where('name', 'Egyéb')->first(),
            "Miopia, Lombaris Diszkropatia" => ChronicIllness::where('name', 'Egyéb')->first(),
            "magasabb vércukorszínt" => ChronicIllness::where('name', 'Cukorbetegség')->first(),
            "Nincs" => ChronicIllness::where('name', 'Nincs krónikus betegsége')->first(),
            "enyhe hörgőasztma" => ChronicIllness::where('name', 'Egyéb')->first(),
            "Asztma, szív elégtelenség" => ChronicIllness::where('name', 'Egyéb')->first(),
            "atópiás dermatitisz" => ChronicIllness::where('name', 'Egyéb')->first(),
            "Astma Bronsic" => ChronicIllness::where('name', 'Krónikus légzési elégtelenség')->first(),
            "Marshall szindróma" => ChronicIllness::where('name', 'Egyéb')->first(),
            "Pitvari Septum Defectus (ASD), Atópiás asztma, Allergiás Rhinitis" => ChronicIllness::where('name', 'Egyéb')->first()
        ];

        $this->foodSensitivitiesMap = [
            'az én ételem nem érzékeny' => null,
            'Vegán' => FoodSensitivity::where('name', 'Egyéb')->first(),
            '-' => null,
            'Tartósító szerek' => FoodSensitivity::where('name', 'Egyéb')->first(),
            'tejfehérje' => FoodSensitivity::where('name', 'tejfehérje (kazein)')->first(),
            'disznóhúsra' => FoodSensitivity::where('name', 'Egyéb')->first(),
            'lakóz érzékeny' => FoodSensitivity::where('name', 'Egyéb')->first(),
            'tej' => FoodSensitivity::where('name', 'tejfehérje (kazein)')->first(),
            'glutén érzékeny' => FoodSensitivity::where('name', 'Egyéb')->first(),
            'Tejfehérje-érzékenység, nem fogyaszthat semmiféle tejszármazékot' => FoodSensitivity::where('name', 'tejfehérje (kazein)')->first(),
            'Édes tejen kívűl nem eszik meg semmit "ami fehér".' => FoodSensitivity::where('name', 'Egyéb')->first(),
            'sajtfélék' => FoodSensitivity::where('name', 'Egyéb')->first(),
            'Nincs.' => null,
            'tojás' => FoodSensitivity::where('name', 'tojás')->first(),
            'élelmiszer-adalékanyag intolerancia' => FoodSensitivity::where('name', 'Egyéb')->first(),
            'csoki' => FoodSensitivity::where('name', 'Egyéb')->first(),
            'Tojás - enyhe' => FoodSensitivity::where('name', 'tojás')->first(),
            'Tejfehérje' => FoodSensitivity::where('name', 'tejfehérje (kazein)')->first(),
            'üditő, méz, édességek' => FoodSensitivity::where('name', 'Egyéb')->first(),
            'Glutén érzékenység' => FoodSensitivity::where('name', 'Egyéb')->first(),
            'Eper' => FoodSensitivity::where('name', 'eper')->first(),
            'Laktóz' => FoodSensitivity::where('name', 'tejfehérje (kazein)')->first(),
            'Tej' => FoodSensitivity::where('name', 'tejfehérje (kazein)')->first(),
            'magvas' => FoodSensitivity::where('name', 'Egyéb')->first(),
            'Ízesített joghurt' => FoodSensitivity::where('name', 'Egyéb')->first(),
            'hal és bármilyen halas étel' => FoodSensitivity::where('name', 'Egyéb')->first(),
            'Laktóz érzékeny' => FoodSensitivity::where('name', 'tejfehérje (kazein)')->first(),
            'máj' => FoodSensitivity::where('name', 'Egyéb')->first(),
            'NINCS' => null,
            'Laktóz, glutén' => FoodSensitivity::where('name', 'Egyéb')->first(),
            'csokoládé, kakaó' => FoodSensitivity::where('name', 'Egyéb')->first(),
            'Nincs' => null,
            'Laktózérzékenység' => FoodSensitivity::where('name', 'tejfehérje (kazein)')->first(),
            'Liszt érzékenység (glutén intolerancia)' => FoodSensitivity::where('name', 'liszt')->first(),
            'ételszínezék' => FoodSensitivity::where('name', 'Egyéb')->first(),
            'eper' => FoodSensitivity::where('name', 'eper')->first(),
            'laktóz' => FoodSensitivity::where('name', 'tejfehérje (kazein)')->first(),
            'aprómagvas gyümölcsök' => FoodSensitivity::where('name', 'Egyéb')->first(),
            'tejtermék' => FoodSensitivity::where('name', 'tejfehérje (kazein)')->first(),
            'Búzafehérje intolerancia' => FoodSensitivity::where('name', 'Egyéb')->first(),
            'nincs' => null,
            'aszalt barack' => FoodSensitivity::where('name', 'Egyéb')->first(),
            'narancs, kiwi' => FoodSensitivity::where('name', 'Egyéb')->first(),
            'laktózra, izfozokra, szinezekre' => FoodSensitivity::where('name', 'Egyéb')->first(),
        ];
    }

    public function organizations() {
    }

    public function scouts() {
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
        }

        //import patrols
        foreach ($data['szervezet.ors'] as $org) {
            $fields = $org->fields;
            if (empty($fields->csapat) && empty($fields->raj)) {
                Log::warning("Can not import: $org->model - name: $fields->nev - composed-slug: $fields->composed_slug; 'csapat' and 'raj' is empty");
                continue;
            }
            $troop = Troop::where('slug', $fields->raj)->first()->id ?? null;
            $team_id  = Team::where('slug', $fields->csapat)->first()->id ?? $troop->team_id;
            if (empty($team_id) && empty($troop_id)) {
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
//            print_r($fields->korosztaly[0]);
            $patrol->age_group_id                   = isset($fields->korosztaly[0]) && isset($this->ageGroupMap[$fields->korosztaly[0]]) ? $this->ageGroupMap[$fields->korosztaly[0]] : $this->ageGroupMap['v'];

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

//        $foodSensitiv = $scoutsData->unique('fields.jellemzok.id_20')->pluck('fields.jellemzok.id_20')->toArray();
//
//        unset($foodSensitiv[0]);
//        dd(array_flip($foodSensitiv));

//        $i = 0;
        foreach ($scoutsData as $scout) {
//            $i++;
//            if ($i > 5) return;

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

            $scout = Scout::firstOrNew (
                [
                    'team_id'           => $team_id,
                    'troop_id'          => $troop->id ?? null,
                    'patrol_id'         => $patrol->id ?? null,
                    'ecset_code'        => $fields->ecsk, //TODO check
                ]
            );

            $scout->name_prefix                    = $fields->nev_elotag ?? null;
            $scout->family_name                    = $fields->nev ?? null;
            $scout->given_name                     = $fields->keresztnev ?? null;
            $scout->nickname                       = $fields->becenev ?? null;
            $scout->email                          = $fields->email ?? null;
            $scout->phone                          = $fields->telefonszam ?? null;
            $scout->personal_identification_number = !empty($fields->jellemzok->id_12) ? substr($fields->jellemzok->id_12, 0, 20) : null;
            $scout->gender                         = $this->genderMap[$fields->nem] ?? null;
            $scout->is_active                      = $this->statusMap[$fields->statusz] ?? null;
            $scout->legal_relationship_id          = isset($fields->jogviszony[0]) ?
                ($this->legalRelationshipMap[$fields->jogviszony[0]] ?? $this->legalRelationshipMap['ervenytelen'])
                : $this->legalRelationshipMap['ervenytelen'];
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

//            $scout->registration_form              = $fields->______________REPLACE__________ ?? null;

            if (!empty($fields->kep)) {
                $path = '/storage/app/media/importedimages/' . $fields->kep;
                $url = url('/') . $path;
                if ((new Filesystem())->existsInsensitive(base_path() . $path)) {
                    $file = new File;
                    $file->fromUrl($url);
                    $scout->profile_image()->add($file);
                } else {
                    Log::warning("Can't attach file $url.");
                }
            }
        }
    }
}
