<?php

namespace Csatar\Csatar\Classes\Mappers;

use Rainlab\Location\Models\Country;

class CountryMapper
{
    public array $idToCountryCode = [];
    public array $countryCodeToId = [];

    public function __construct(){
        $this->mapCountries();
    }

    private function mapCountries(){
        $countries = Country::all();
        foreach ($countries as $country) {
            $this->idToCountryCode[$country->id]   = $country->code;
            $this->countryCodeToId[$country->code] = $country->id;
        }
    }

}
