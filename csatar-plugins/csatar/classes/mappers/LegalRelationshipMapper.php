<?php

namespace Csatar\Csatar\Classes\Mappers;

use Csatar\Csatar\Models\LegalRelationship;

class LegalRelationshipMapper
{
    public array $idsToNames = [];
    public array $namesToIds = [];

    function __construct(){
        $this->mapLegalRelationships();
    }

    private function mapLegalRelationships(){
        $legalRelationships = LegalRelationship::all();
        foreach ($legalRelationships as $legalRelationship) {
            $this->idsToNames[$legalRelationship->id]   = $legalRelationship->name;
            $this->namesToIds[$legalRelationship->name] = $legalRelationship->id;
        }
    }
}
