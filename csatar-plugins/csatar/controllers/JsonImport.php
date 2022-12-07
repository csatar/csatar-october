<?php namespace Csatar\Csatar\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Csatar\Csatar\Classes\Enums\Gender;
use Csatar\Csatar\Models\District;
use Csatar\Csatar\Models\Association;
use Csatar\Csatar\Models\AgeGroup;
use Csatar\Csatar\Classes\Enums\Status;
use Csatar\Csatar\Models\Patrol;
use Csatar\Csatar\Models\Team;
use Csatar\Csatar\Models\Troop;
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
            "Arad" => "Arad",
            "Argyas" => "Argeș",
            "Bákó" => "Bacău",
            "Beszterce-Naszód" => "Bistrița-Năsăud",
            "Bihar" => "Bihor",
            "Buzó" => "Buzău",
            "Brassó" => "Brașov",
            "Fehér" => "Alba",
            "Galac" => "Galați",
            "Hargita" => "Harghita",
            "Hunyad" => "Hunedoara",
            "Kolozs" => "Cluj",
            "Kostanca" => "Constanța",
            "Kovászna" => "Covasna",
            "Krassó-Szörény" => "Caraș-Severin",
            "Máramaros" => "Maramureș",
            "Maros" => "Mureș",
            "Szatmár" => "Satu Mare",
            "Szeben" => "Sibiu",
            "Szilágy" => "Sălaj",
            "Temes" => "Timiș",
            "Vráncsa" => "Vrancea",
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

    public function organizations() {

    }

    public function onUploadAndProcess() {
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
}
