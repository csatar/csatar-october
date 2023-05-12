<?php

namespace Csatar\KnowledgeRepository\Components;

use Auth;
use Cms\Classes\ComponentBase;
use Csatar\Forms\Components\BasicForm;
use Csatar\Csatar\Components\Partials;
use Lang;
use Redirect;

class WorkPlansList extends ComponentBase
{

    public $organizationUnit;

    public $workPlans;

    public $isPatrolPage;

    public $isTroopPage;

    public function componentDetails()
    {
        return [
            'name' => Lang::get('csatar.knowledgerepository::lang.plugin.components.workPlansList.name'),
            'description' => Lang::get('csatar.knowledgerepository::lang.plugin.components.workPlansList.description'),
        ];
    }

    public function onRender()
    {
        $this->organizationUnit = $this->page->controller->vars["basicForm"]->record;
        $this->isPatrolPage = get_class($this->organizationUnit) == 'Csatar\Csatar\Models\Patrol' ? true : false;
        $this->isTroopPage = get_class($this->organizationUnit) == 'Csatar\Csatar\Models\Troop' ? true : false;
        $this->workPlans = $this->getWorkPlans();
    }

    public function getWorkPlans()
    {
        $workPlans = isset($this->organizationUnit->workPlans) ? $this->organizationUnit->workPlans : $this->organizationUnit->team->workPlans;

        if ($this->isTroopPage) {
            $ovamtvWorkPlans = $this->organizationUnit->patrols->load('ovamtvWorkPlans.patrol')->pluck('ovamtvWorkPlans')->flatten();
        } else {
            $ovamtvWorkPlans = isset($this->organizationUnit->ovamtvWorkPlans) ? $this->organizationUnit->ovamtvWorkPlans->load('patrol') : collect([]);
        }

        $workPlansDates = !$this->isPatrolPage && !$this->isTroopPage ? $workPlans->pluck('year')->toArray() : [];
        $ovamtvWorkPlansDates = $ovamtvWorkPlans->pluck('start_date')->map(function ($date) {
            return $this->getScountingYear($date);
        })->toArray();

        $yearsWithWorkPlanYears = array_unique(array_merge($workPlansDates, $ovamtvWorkPlansDates));
        asort($yearsWithWorkPlanYears);

        $ovamtvWorkPlansByYear = $ovamtvWorkPlans->groupBy(function ($item, $key) {
            return $this->getScountingYear($item->start_date);
        });

        $workPlansByYears = [];

        foreach ($yearsWithWorkPlanYears as $year) {
            $workPlansByYears[$year] = [
                'teamWorkPlan' => $workPlans->where('year', $year)->first(),
                'ovamtvWorkPlans' => isset($ovamtvWorkPlansByYear[$year]) ? $this->getOvamtvsWithPatrolName($ovamtvWorkPlansByYear[$year]) : [],
            ];
        }

        return $workPlansByYears;
    }

    public function getOvamtvsWithPatrolName($ovamtvWorkPlans) {
        return $ovamtvWorkPlans->map(function ($ovamtv) {
            $ovamtv['patrol_name'] = $ovamtv->patrol->extended_name;
            return $ovamtv;
        })
        ->sortBy('start_date')
        ->groupBy('patrol_id');

    }

    public function getScountingYear($dateString) {
        $date = new \DateTime($dateString);
        $year = $date->format('Y');
        $month = $date->format('m');
        if ($month < 9) {
            $year--;
        }
        return (int) $year;
    }
}
