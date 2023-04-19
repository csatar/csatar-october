<?php
namespace Csatar\Csatar\Components;

use Auth;
use Carbon\Carbon;
use Cms\Classes\ComponentBase;
use Csatar\Csatar\Models\AccidentLogRecord;
use Csatar\Csatar\Classes\CsvCreator;
use Lang;
use Storage;
use Response;
use Redirect;

class AccidentLogRecordList extends ComponentBase
{
    public $recordList;
    public $attributesWithLabels;
    public $columnsToDisplay;

    public function componentDetails()
    {
        return [
            'name' => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.accidentLog'),
            'description' => Lang::get('csatar.csatar::lang.plugin.component.accidentLog.accidentLogRecordList')
        ];
    }

    public function onRun(){
        $this->prepareVars();
    }

    public function prepareVars() {
        $this->recordList           = AccidentLogRecord::all();
        $this->attributesWithLabels = AccidentLogRecord::getAttributesWithLabels();
        $this->columnsToDisplay     = AccidentLogRecord::getAttributesWithLabels(true);
    }

    public function onExportToCsv(){
        $fileName = Carbon::today()->toDateString() . '.csv';
        $csvPath  = temp_path() . '/' . $fileName;
        $this->prepareVars();

        $data = [
            array_values($this->attributesWithLabels),
        ];

        foreach ($this->recordList as $record) {
            $dataRow = [];
            foreach (array_keys($this->attributesWithLabels) as $attribute) {
                if ($attribute == 'attachmentLinks') {
                    $dataRow[] = implode(', ', array_values($record->{$attribute}->toArray()));
                } else {
                    $dataRow[] = $record->{$attribute};
                }
            }

            $data[] = $dataRow;
        }

        CsvCreator::writeCsvFile($csvPath, $data);

        return Redirect::to('letoltes/csv/' . $fileName);
    }

}
