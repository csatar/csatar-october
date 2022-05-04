<?php namespace Csatar\Forms;

use System\Classes\PluginBase;


class Plugin extends PluginBase
{
    /**
     * Component details
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name'        => 'csatar.forms::lang.plugin.name',
            'description' => 'csatar.forms::lang.plugin.description',
            'icon'        => 'icon-paperclip',
        ];
    }

    /**
     * Registers components
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Csatar\Forms\Components\BasicForm'   => 'basicForm',
        ];
    }

}
