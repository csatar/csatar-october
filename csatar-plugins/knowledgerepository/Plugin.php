<?php
namespace Csatar\KnowledgeRepository;

use Csatar\KnowledgeRepository\Models\Region;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public $require = ['Csatar.Csatar'];

    public function registerComponents()
    {
        return [
            \Csatar\KnowledgeRepository\Components\GameForm::class => 'gameForm',
            \Csatar\KnowledgeRepository\Components\MethodologyForm::class => 'methodologyForm',
            \Csatar\KnowledgeRepository\Components\SongForm::class => 'songForm',
            \Csatar\KnowledgeRepository\Components\WorkPlansList::class => 'workPlansList',
        ];
    }

    public function registerSettings()
    {
    }

    public function registerPDFLayouts()
    {
        return [
            'csatar.knowledgerepository::pdf.layouts.workplanlayout',
            'csatar.knowledgerepository::pdf.layouts.weeklyworkplanlayout',
            'csatar.knowledgerepository::pdf.layouts.ovaworkplanlayout',
        ];
    }

    public function registerPDFTemplates()
    {
        return [
            'csatar.knowledgerepository::pdf.workplantemplate',
            'csatar.knowledgerepository::pdf.weeklyworkplantemplate',
            'csatar.knowledgerepository::pdf.ovaworkplantemplate',
        ];
    }

    public function registerListColumnTypes()
    {
        return [
            'regionExtendedName' => [$this, 'getRegionExtendedName'],
        ];
    }

    public function getRegionExtendedName($value, $column, $record)
    {
        $region = Region::where('name', $value)->first() ?? null;
        return $region ? $region->getExtendedNameAttribute() : '';
    }

}
