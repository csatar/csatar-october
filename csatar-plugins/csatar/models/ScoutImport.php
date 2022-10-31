<?php namespace Csatar\Csatar\Models;

use Csatar\Csatar\Models\Scout;

class ScoutImport extends \Backend\Models\ImportModel
{
    /**
     * @var array The rules to be applied to the data.
     */
    public $rules = [];

    public function importData($results, $sessionKey = null)
    {
        foreach ($results as $row => $data) {

            try {
                $subscriber = new Scout;
                $subscriber->fill($data);
                $subscriber->save();

       //         $this->logCreated();
            }
            catch (\Exception $ex) {
       //         $this->logError($row, $ex->getMessage());
            }
        }
    }
}
