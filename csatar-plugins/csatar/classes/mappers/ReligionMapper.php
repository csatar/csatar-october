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
        $legalRelationships = Religion::all();
        foreach ($legalRelationships as $legalRelationship) {
            $this->idsToNames[$legalRelationship->id]   = $legalRelationship->name;
            $this->namesToIds[$legalRelationship->name] = $legalRelationship->id;
        }
    }
}
