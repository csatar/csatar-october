<?php

namespace Csatar\Csatar\Classes\Xlsx;

// import class
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ScoutXlsxImport implements ToCollection
{
    public $data = [];

    public function collection(Collection $rows)
    {
        $this->data = $rows;
    }
}
