<?php

namespace Csatar\Csatar\Classes\Xlsx;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class ScoutXlsxExport implements FromCollection, WithHeadings, WithEvents
{
    private $numberOfHeadingRows;
    private $headings = [];
    private $data = [];

    public function __construct(int $numberOfHeadingRows, $data)
    {
        $this->numberOfHeadingRows = $numberOfHeadingRows;
        $this->headings = $this->getHeadingRows($numberOfHeadingRows);
        $this->data = $data;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function registerEvents(): array
    {
        $freezeRow = 'A' . ($this->numberOfHeadingRows + 1);
        return [
            AfterSheet::class => function(AfterSheet $event) use ($freezeRow) {
                $event->sheet->freezePane($freezeRow, $freezeRow);
            },
        ];
    }

    public function collection()
    {
        return collect($this->data);
    }

    private function getHeadingRows($numberOfHeadingRows)
    {
        $headingRows = [];
        for ($i = 0; $i < $numberOfHeadingRows; $i++) {
            $headingRows[] = [];
        }
        return $headingRows;
    }
}
