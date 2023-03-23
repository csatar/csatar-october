<?php namespace Csatar\Csatar\Traits;

use Csatar\Csatar\Classes\HistoryService;

trait History
{
    /**
     * @var array The attributes that should excluded from history.
     *
     * public $exlcudedFromHistory = [];
     */

    /**
     * @var const HISTORY_RELATION_NAME changes the relation name used to store history.
     * const HISTORY_RELATION_NAME = 'history';
     */

    /**
     * @var bool historyEnabled flag for arbitrarily disabling history.
     */
    public $historyEnabled = true;

    /**
     * initializeHistory trait for a model.
     * @return void
     */
    public function initializeHistory()
    {
        if (!$this->historyEnabled) {
            return;
        }

        HistoryService::bindEventsToModel($this);
    }
}
