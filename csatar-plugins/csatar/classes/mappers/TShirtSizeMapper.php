<?php

namespace Csatar\Csatar\Classes\Mappers;

use Csatar\Csatar\Models\TShirtSize;

class TShirtSizeMapper
{
    public array $idsToNames = [];
    public array $namesToIds = [];

    public function __construct(){
        $this->mapTShirtSizes();
    }

    private function mapTShirtSizes(){
        $tShirtSizes = TShirtSize::all();
        foreach ($tShirtSizes as $tShirtSize) {
            $this->idsToNames[$tShirtSize->id]   = $tShirtSize->name;
            $this->namesToIds[$tShirtSize->name] = $tShirtSize->id;
        }
    }
}
