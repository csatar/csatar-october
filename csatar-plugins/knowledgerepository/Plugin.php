<?php namespace Csatar\KnowledgeRepository;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public $require = ['Csatar.Csatar'];

    public function registerComponents()
    {
        return [
            \Csatar\KnowledgeRepository\Components\GameForm::class => 'gameForm',
            \Csatar\KnowledgeRepository\Components\MethodologyForm::class => 'methodologyForm',
        ];
    }

    public function registerSettings()
    {
    }

    public function registerPDFLayouts()
    {
        return [
            'csatar.knowledgerepository::pdf.layouts.workplanlayout',
        ];
    }

    public function registerPDFTemplates()
    {
        return [
            'csatar.knowledgerepository::pdf.workplantemplate',
        ];
    }
}
