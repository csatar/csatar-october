<?php namespace Csatar\KnowledgeRepository;

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
        ];
    }

    public function registerSettings()
    {
    }

    public function registerListColumnTypes()
    {
        return [
            'regionExtendedName' => [$this, 'getRegionExtendedName'],
        ];
    }

    public function getRegionExtendedName($value, $column, $record)
    {
        $region = Region::where('name', $value)->first();
        return $region->getExtendedNameAttribute();
    }
}
