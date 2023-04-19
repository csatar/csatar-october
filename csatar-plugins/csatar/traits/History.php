<?php
namespace Csatar\Csatar\Traits;

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
     * @var bool historyDisabled flag for arbitrarily disabling history.
     */

    /**
     * initializeHistory trait for a model.
     * @return void
     */
    public function initializeHistory()
    {
        if (HistoryService::isHistoryDisabled($this)) {
            return;
        }

        HistoryService::addHistoryRelationToModel($this);
        HistoryService::bindEventsToModel($this);
    }

}
