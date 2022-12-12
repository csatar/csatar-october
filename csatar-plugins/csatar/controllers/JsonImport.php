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
            'a' => LegalRelationship::firstOrCreate(['name' => 'Alakuló csapat tag'])->first()->id,
            'ujonc' => LegalRelationship::firstOrCreate(['name' => 'Újonc'])->first()->id,
            'tag' => LegalRelationship::firstOrCreate(['name' => 'Tag'])->first()->id,
            'ttag' => LegalRelationship::firstOrCreate(['name' => 'Tiszteletbeli tag'])->first()->id,
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
            "Nincs" => $allergies->where('name', 'Nincs'),
            "Darázscsípés" => $allergies->where('name', 'Rovarméreg allergia'),
            "Por, pollen, macskaszőr, árpa" => $allergies->where('name', 'Egyéb'),
            "az idiótákra" => $allergies->where('name', 'Nincs'),
            "por" => $allergies->where('name', 'Egyéb'),
            "-" => $allergies->where('name', 'Nincs'),
            "házi por, őszi mezei füvek, dió" => $allergies->where('name', 'Egyéb'),
            "Brufen, szalicil/ szalicilsav" => $allergies->where('name', 'Egyéb'),
            "Pollen" => $allergies->where('name', 'Pollen alergia/Szénanátha'),
            "Tartósító szerek" => $allergies->where('name', 'Egyéb'),
            "Enyhe allergia rovarcsípésekre" => $allergies->where('name', 'Rovarméreg allergia'),
            "poratka, toll" => $allergies->where('name', 'Egyéb'),
            "Házi por, macska, széna, porzós növények, állatok" => $allergies->where('name', 'Egyéb'),
            "Penicilin érzékenység" => $allergies->where('name', 'Gyógyszerallergia'),
            "Furazonido, Biseptol" => $allergies->where('name', 'Gyógyszerallergia'),
            "paracetamolra" => $allergies->where('name', 'Gyógyszerallergia'),
            "meh csipes" => $allergies->where('name', 'Rovarméreg allergia'),
            "Gyapjú" => $allergies->where('name', 'Egyéb'),
            "Parlagfű" => $allergies->where('name', 'Pollen alergia/Szénanátha'),
            "metoclopramid" => $allergies->where('name', 'Gyógyszerallergia'),
            "fű és por" => $allergies->where('name', 'Egyéb'),
            "Citrom, citromsav, citromsó. (margarin, üzleti keksz, süti, torta, üditő, fagyi, cukorka, felvágott, virsli, ketchup. Piros kiütés a bőrön, nehezen múlik)" => $allergies->where('name', 'Egyéb'),
            "Codein,Nurofen" => $allergies->where('name', 'Gyógyszerallergia'),
            "Penicillin érzékenység" => $allergies->where('name', 'Gyógyszerallergia'),
            "poratka, házipor" => $allergies->where('name', 'Egyéb'),
            "ampicilin" => $allergies->where('name', 'Gyógyszerallergia'),
            "por, pollen" => $allergies->where('name', 'Egyéb'),
            "poratka" => $allergies->where('name', 'Egyéb'),
            "parlagfű, pázsitfű" => $allergies->where('name', 'Pollen alergia/Szénanátha'),
            "a kórházi sebtapaszra" => $allergies->where('name', 'Egyéb'),
            "Amoxicilin" => $allergies->where('name', 'Gyógyszerallergia'),
            "Pollen és penészgomba" => $allergies->where('name', 'Egyéb'),
            "penész, por, kazein" => $allergies->where('name', 'Egyéb'),
            "astm –bronsic alergic" => $allergies->where('name', 'Egyéb'),
            "Tejfehérje allergia, intolerancia" => $allergies->where('name', 'Ételintollerancia'),
            "pollen" => $allergies->where('name', 'Pollen alergia/Szénanátha'),
            "gyapjú, pollenek" => $allergies->where('name', 'Egyéb'),
            "tejérzékeny" => $allergies->where('name', 'Ételintollerancia'),
            "Algocalmin" => $allergies->where('name', 'Gyógyszerallergia'),
            "Pollen, kutya - és macskaszőr" => $allergies->where('name', 'Egyéb'),
            "méhcsipés" => $allergies->where('name', 'Rovarméreg allergia'),
            "kutyaszőr, por és poratka" => $allergies->where('name', 'Egyéb'),
            "Rovarcsípés" => $allergies->where('name', 'Rovarméreg allergia'),
            "darázscsípés" => $allergies->where('name', 'Rovarméreg allergia'),
            "lázcsillapító" => $allergies->where('name', 'Gyógyszerallergia'),
            "Nincs." => $allergies->where('name', 'Nincs'),
            "Méhcsípés" => $allergies->where('name', 'Rovarméreg allergia'),
            "levendula" => $allergies->where('name', 'Egyéb'),
            "penicillin" => $allergies->where('name', 'Gyógyszerallergia'),
            "ibuprofen" => $allergies->where('name', 'Gyógyszerallergia'),
            "Bronsitis, Aszma" => $allergies->where('name', 'Egyéb'),
            "poratka, pollenallergia" => $allergies->where('name', 'Egyéb'),
            "nap" => $allergies->where('name', 'Egyéb'),
            "tej" => $allergies->where('name', 'Ételintollerancia'),
            "Por" => $allergies->where('name', 'Egyéb'),
            "Ospen, Rovarcsipések" => $allergies->where('name', 'Egyéb'),
            "Penicilin" => $allergies->where('name', 'Gyógyszerallergia'),
            "Van, de nem tudja mi váltja ki" => $allergies->where('name', 'Egyéb'),
            "hársfa pollen" => $allergies->where('name', 'Pollen alergia/Szénanátha'),
            "Por, Pollen, Penesz" => $allergies->where('name', 'Egyéb'),
            "por, gluten erzekeny" => $allergies->where('name', 'Egyéb'),
            "gluten" => $allergies->where('name', 'Ételintollerancia'),
            "Kakaó" => $allergies->where('name', 'Ételintollerancia'),
            "Antibiotukim" => $allergies->where('name', 'Gyógyszerallergia'),
            "Virágporok, gabonaporok" => $allergies->where('name', 'Egyéb'),
            "nincs" => $allergies->where('name', 'Nincs'),
            "Eurespal" => $allergies->where('name', 'Gyógyszerallergia'),
            "Paracetamol" => $allergies->where('name', 'Gyógyszerallergia'),
            "szénanátha" => $allergies->where('name', 'Pollen alergia/Szénanátha'),
            "házi por, pázsitfű" => $allergies->where('name', 'Egyéb'),
            "Poratka" => $allergies->where('name', 'Egyéb'),
            "Eper" => $allergies->where('name', 'Ételintollerancia'),
            "Biseptor" => $allergies->where('name', 'Gyógyszerallergia'),
            "Porallergia" => $allergies->where('name', 'Egyéb'),
            "enyhe tejallergia (nagyobb mennyiségnél)" => $allergies->where('name', 'Ételintollerancia'),
            "Hányingercsillapító" => $allergies->where('name', 'Gyógyszerallergia'),
            "Cefalexin gyógyszere" => $allergies->where('name', 'Gyógyszerallergia'),
            "Ízesített joghurt" => $allergies->where('name', 'Ételintollerancia'),
            "széna, házipor atka" => $allergies->where('name', 'Egyéb'),
            "Csípések (pók, szúnyog, méh, darázs)" => $allergies->where('name', 'Rovarméreg allergia'),
            "por, penész, macskaszőr" => $allergies->where('name', 'Egyéb'),
            "hal" => $allergies->where('name', 'Ételintollerancia'),
            "Augmentin, Debridat" => $allergies->where('name', 'Gyógyszerallergia'),
            "Kutyaszőr" => $allergies->where('name', 'Egyéb'),
            "Szója" => $allergies->where('name', 'Ételintollerancia'),
            "Rovar csipés" => $allergies->where('name', 'Rovarméreg allergia'),
            "Penicilin családra" => $allergies->where('name', 'Gyógyszerallergia'),
            "Ibuprofen" => $allergies->where('name', 'Gyógyszerallergia'),
            "májkrém" => $allergies->where('name', 'Ételintollerancia'),
            "eritromicin" => $allergies->where('name', 'Gyógyszerallergia'),
            "Virágpor, szúnyogcsípés (kezelés alatt)" => $allergies->where('name', 'Egyéb'),
            "darázs csipés" => $allergies->where('name', 'Rovarméreg allergia'),
            "NINCS" => $allergies->where('name', 'Nincs'),
            "macska és házipor" => $allergies->where('name', 'Egyéb'),
            "Por. tol, állat ször, penész" => $allergies->where('name', 'Egyéb'),
            "Preduiszon/predniszon gyógyszerek" => $allergies->where('name', 'Gyógyszerallergia'),
            "nap :)" => $allergies->where('name', 'Egyéb'),
            "napsütés, epa, paradicsom" => $allergies->where('name', 'Egyéb'),
            "Szúnyogcsípés, macskaszőr" => $allergies->where('name', 'Egyéb'),
            "Nurofen, Brufen" => $allergies->where('name', 'Gyógyszerallergia'),
            "dió" => $allergies->where('name', 'Ételintollerancia'),
            "Élelmiszerpenész" => $allergies->where('name', 'Ételintollerancia'),
            "I tipusu cukorbeteg" => $allergies->where('name', 'Egyéb'),
            "csokolade, kakao, tej" => $allergies->where('name', 'Ételintollerancia'),
            "Porotka, Penicillin, tollú" => $allergies->where('name', 'Egyéb'),
            "Por és atka allergia" => $allergies->where('name', 'Egyéb'),
            "Méhecske csípés" => $allergies->where('name', 'Rovarméreg allergia'),
            "kontakt dermatitisz - bizonyos növényekre a természetből" => $allergies->where('name', 'Egyéb'),
            "Gyógyszer: cefalosporin, novosept, zinnat" => $allergies->where('name', 'Gyógyszerallergia'),
            "SUMETROLIM" => $allergies->where('name', 'Gyógyszerallergia'),
            "rovarcsípés" => $allergies->where('name', 'Rovarméreg allergia'),
            "Gyógyszer" => $allergies->where('name', 'Gyógyszerallergia'),
            "Penicilin és száemazékai" => $allergies->where('name', 'Gyógyszerallergia'),
            "zinat" => $allergies->where('name', 'Gyógyszerallergia'),
            "napallergia" => $allergies->where('name', 'Egyéb'),
            "Gumicukor" => $allergies->where('name', 'Ételintollerancia'),
            "Por, akta, penész" => $allergies->where('name', 'Egyéb'),
            "dió, hárs pollen" => $allergies->where('name', 'Egyéb'),
            "por, pollen, amoxacilin" => $allergies->where('name', 'Egyéb'),
            "???" => $allergies->where('name', 'Nincs'),
            "szeder" => $allergies->where('name', 'Ételintollerancia'),
            "Sumatrolin" => $allergies->where('name', 'Gyógyszerallergia'),
            "macskaszőr, poratkák" => $allergies->where('name', 'Egyéb'),
            "laktóz" => $allergies->where('name', 'Ételintollerancia'),
            "porallergia, tünetmentes" => $allergies->where('name', 'Egyéb'),
            "por allergia" => $allergies->where('name', 'Egyéb'),
            "darázscsípésre" => $allergies->where('name', 'Rovarméreg allergia'),
            "méhcsípés" => $allergies->where('name', 'Rovarméreg allergia'),
            "méh csípés" => $allergies->where('name', 'Rovarméreg allergia'),
            "Dió, Paradicsom, Vinete" => $allergies->where('name', 'Ételintollerancia'),
            "Vancomicin gyógyszer" => $allergies->where('name', 'Gyógyszerallergia'),
            "időszakos ekcéma" => $allergies->where('name', 'Egyéb'),
        ];

        $chronicIllnes = ChronicIllness::all();
        $this->chronicIllnesMap = [
            "NINCS" => $chronicIllnes->where('name', 'Nincs krónikus betegsége'),
            "komolytalanság?!" => $chronicIllnes->where('name', 'Nincs krónikus betegsége'),
            "cukorbetegség" => $chronicIllnes->where('name', 'Cukorbetegség'),
            "-" => $chronicIllnes->where('name', 'Nincs krónikus betegsége'),
            "I tipusú diabétesz, hipertónia" => $chronicIllnes->where('name', 'Egyéb'),
            "Bokasüllyedés, lúdtalp" => $chronicIllnes->where('name', 'Mozgásszervi betegségek'),
            "magas vérnyomás" => $chronicIllnes->where('name', 'Magas vérnyomás'),
            "asztma" => $chronicIllnes->where('name', 'Krónikus légzési elégtelenség'),
            "pajzsmirigy gyulladás" => $chronicIllnes->where('name', 'Pajzsmirigy működési zavar'),
            "Cukorbeteg" => $chronicIllnes->where('name', 'Cukorbetegség'),
            "magas vérnyomásra hajlamos" => $chronicIllnes->where('name', 'Magas vérnyomás'),
            "Magas vérnyomás" => $chronicIllnes->where('name', 'Magas vérnyomás'),
            "hypothyreosis" => $chronicIllnes->where('name', 'Egyéb'),
            "cukorbaj" => $chronicIllnes->where('name', 'Cukorbetegség'),
            "Allergia, asztma" => $chronicIllnes->where('name', 'Egyéb'),
            "Asztma" => $chronicIllnes->where('name', 'Krónikus légzési elégtelenség'),
            "Kataplexia  Narkolepszia szindróma" => $chronicIllnes->where('name', 'Egyéb'),
            "magas got. szint (máj)" => $chronicIllnes->where('name', 'Egyéb'),
            "tüdő TBC" => $chronicIllnes->where('name', 'Egyéb'),
            "gerincferdülés" => $chronicIllnes->where('name', 'Mozgásszervi betegségek'),
            "Nincsenek." => $chronicIllnes->where('name', 'Nincs krónikus betegsége'),
            "Diabétesz" => $chronicIllnes->where('name', 'Egyéb'),
            "penicilin érzékeny" => $chronicIllnes->where('name', 'Egyéb'),
            "Forgókopás" => $chronicIllnes->where('name', 'Mozgásszervi betegségek'),
            "trombocitoremia" => $chronicIllnes->where('name', 'Egyéb'),
            "nincs" => $chronicIllnes->where('name', 'Nincs krónikus betegsége'),
            "Veleszületett szívrendellenesség (RO: malformatie la inima)" => $chronicIllnes->where('name', 'Szívelégtelenség'),
            "szivzorej" => $chronicIllnes->where('name', 'Szívelégtelenség'),
            "Agyhalál néha, vagy mindig 🤔" => $chronicIllnes->where('name', 'Nincs krónikus betegsége'),
            "Asztma (nem súlyos)" => $chronicIllnes->where('name', 'Krónikus légzési elégtelenség'),
            "cukorbetegség, inzulinfüggő" => $chronicIllnes->where('name', 'Cukorbetegség'),
            "ADHD" => $chronicIllnes->where('name', 'Egyéb'),
            "Astm bronsic" => $chronicIllnes->where('name', 'Krónikus légzési elégtelenség'),
            "1 tipusú diabéttesz, inzulinfüggő" => $chronicIllnes->where('name', 'Cukorbetegség'),
            "epilepszia" => $chronicIllnes->where('name', 'Egyéb'),
            "Crigler Najjar" => $chronicIllnes->where('name', 'Egyéb'),
            "Miopia, Lombaris Diszkropatia" => $chronicIllnes->where('name', 'Egyéb'),
            "magasabb vércukorszínt" => $chronicIllnes->where('name', 'Cukorbetegség'),
            "Nincs" => $chronicIllnes->where('name', 'Nincs krónikus betegsége'),
            "enyhe hörgőasztma" => $chronicIllnes->where('name', 'Krónikus légzési elégtelenség'),
            "Asztma, szív elégtelenség" => $chronicIllnes->where('name', 'Egyéb'),
            "atópiás dermatitisz" => $chronicIllnes->where('name', 'Egyéb'),
            "Astma Bronsic" => $chronicIllnes->where('name', 'Krónikus légzési elégtelenség'),
            "Marshall szindróma" => $chronicIllnes->where('name', 'Egyéb'),
            "Pitvari Septum Defectus (ASD), Atópiás asztma, Allergiás Rhinitis" => $chronicIllnes->where('name', 'Egyéb')
        ];

        $foodSensitivities = FoodSensitivity::all();
        $this->foodSensitivitiesMap = [
            'az én ételem nem érzékeny' => null,
            'Vegán' => $foodSensitivities->where('name', 'Egyéb'),
            '-' => null,
            'Tartósító szerek' => $foodSensitivities->where('name', 'Egyéb'),
            'tejfehérje' => $foodSensitivities->where('name', 'tejfehérje (kazein)'),
            'disznóhúsra' => $foodSensitivities->where('name', 'Egyéb'),
            'lakóz érzékeny' => $foodSensitivities->where('name', 'Egyéb'),
            'tej' => $foodSensitivities->where('name', 'tejfehérje (kazein)'),
            'glutén érzékeny' => $foodSensitivities->where('name', 'Egyéb'),
            'Tejfehérje-érzékenység, nem fogyaszthat semmiféle tejszármazékot' => $foodSensitivities->where('name', 'tejfehérje (kazein)'),
            'Édes tejen kívűl nem eszik meg semmit "ami fehér".' => $foodSensitivities->where('name', 'Egyéb'),
            'sajtfélék' => $foodSensitivities->where('name', 'Egyéb'),
            'Nincs.' => null,
            'tojás' => $foodSensitivities->where('name', 'tojás'),
            'élelmiszer-adalékanyag intolerancia' => $foodSensitivities->where('name', 'Egyéb'),
            'csoki' => $foodSensitivities->where('name', 'Egyéb'),
            'Tojás - enyhe' => $foodSensitivities->where('name', 'tojás'),
            'Tejfehérje' => $foodSensitivities->where('name', 'tejfehérje (kazein)'),
            'üditő, méz, édességek' => $foodSensitivities->where('name', 'Egyéb'),
            'Glutén érzékenység' => $foodSensitivities->where('name', 'Egyéb'),
            'Eper' => $foodSensitivities->where('name', 'eper'),
            'Laktóz' => $foodSensitivities->where('name', 'Egyéb'),
            'Tej' => $foodSensitivities->where('name', 'Egyéb'),
            'magvas' => $foodSensitivities->where('name', 'Egyéb'),
            'Ízesített joghurt' => $foodSensitivities->where('name', 'Egyéb'),
            'hal és bármilyen halas étel' => $foodSensitivities->where('name', 'Egyéb'),
            'Laktóz érzékeny' => $foodSensitivities->where('name', 'Egyéb'),
            'máj' => $foodSensitivities->where('name', 'Egyéb'),
            'NINCS' => null,
            'Laktóz, glutén' => $foodSensitivities->where('name', 'Egyéb'),
            'csokoládé, kakaó' => $foodSensitivities->where('name', 'Egyéb'),
            'Nincs' => null,
            'Laktózérzékenység' => $foodSensitivities->where('name', 'Egyéb'),
            'Liszt érzékenység (glutén intolerancia)' => $foodSensitivities->where('name', 'liszt'),
            'ételszínezék' => $foodSensitivities->where('name', 'Egyéb'),
            'eper' => $foodSensitivities->where('name', 'eper'),
            'laktóz' => $foodSensitivities->where('name', 'Egyéb'),
            'aprómagvas gyümölcsök' => $foodSensitivities->where('name', 'Egyéb'),
            'tejtermék' => $foodSensitivities->where('name', 'Egyéb'),
            'Búzafehérje intolerancia' => $foodSensitivities->where('name', 'Egyéb'),
            'nincs' => null,
            'aszalt barack' => $foodSensitivities->where('name', 'Egyéb'),
            'narancs, kiwi' => $foodSensitivities->where('name', 'Ételintollerancia'),
            'laktózra, izfozokra, szinezekre' => $foodSensitivities->where('name', 'Egyéb'),
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
