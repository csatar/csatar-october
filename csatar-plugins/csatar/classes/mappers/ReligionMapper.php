<?php

namespace Csatar\Csatar\Classes\Mappers;

use Csatar\Csatar\Models\Religion;

class ReligionMapper
{
    public array $idsToNames = [];
    public array $namesToIds = [];

    public function __construct(){
        $this->mapReligions();
    }

    private function mapReligions(){
        $religions = Religion::all();
        foreach ($religions as $religion) {
            $this->idsToNames[$religion->id]   = $religion->name;
            $this->namesToIds[$religion->name] = $religion->id;
        }
    }
}
