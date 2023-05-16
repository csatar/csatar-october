<?php

namespace Csatar\csatar\models;

use Csatar\Csatar\Classes\Constants;
use Csatar\Csatar\Models\PermissionBasedAccess;

class PatrolWorkPlanBase extends PermissionBasedAccess
{
    public function getMandates($mandateTypeId) {
        $date = $this->created_at ?? date('Y-m-d');

        return $this->patrol->mandates()
            ->where('mandate_type_id', $mandateTypeId)
            ->where('start_date', '<=', $date)
            ->where(function ($query) use ($date) {
                $query->where('end_date', '>=', $date)
                    ->orWhereNull('end_date');
            })
            ->get() ?? null;
    }

    public function getPatrolLeader() {
        $patrolLeaderMandateTypeId = MandateType::where('name', Constants::MANDATE_TYPE_PATROL_LEADER)
            ->where('association_id', $this->getAssociation()->id)
            ->first()->id;

        return $this->getMandates($patrolLeaderMandateTypeId)->first()->scout->full_name ?? null;
    }

    public function getDeputyPatrolLeaders() {
        $deputyPatrolLeaderMandateTypeId = MandateType::where('name', Constants::MANDATE_TYPE_DEPUTY_PATROL_LEADER)
            ->where('association_id', $this->getAssociation()->id)
            ->first()->id;

        $mandates            = $this->getMandates($deputyPatrolLeaderMandateTypeId);
        $deputyPatrolLeaders = [];

        foreach ($mandates as $mandate) {
            $deputyPatrolLeaders[] = $mandate->scout->full_name;
        }

        return implode(', ', $deputyPatrolLeaders);
    }
}