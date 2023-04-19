<?php 
namespace Csatar\Csatar\Components;

use Lang;
use Cms\Classes\ComponentBase;

use Csatar\Csatar\Classes\Enums\Partners;
use Csatar\Csatar\Classes\Enums\Sponsors;
use Csatar\Csatar\Classes\Enums\Discounts;

class Logos extends ComponentBase
{
    public $mode;
    public $title;
    public $logos;
    public $hideSeparator;

    public function componentDetails()
    {
        return [
            'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.name'),
            'description' => Lang::get('csatar.csatar::lang.plugin.component.logos.description'),
        ];
    }

    public function onRender()
    {
        $this->mode          = $this->property('mode');
        $this->hideSeparator = $this->property('hideSeparator');

        switch ($this->mode) {
            case 'sponsors':
                $this->title = Lang::get('csatar.csatar::lang.plugin.component.logos.sponsors.title');
                $this->logos = \Csatar\Csatar\Classes\Enums\Sponsors::getInstance()->sponsors;
                break;

            case 'discounts':
                $this->title = Lang::get('csatar.csatar::lang.plugin.component.logos.discounts.title');
                $this->logos = \Csatar\Csatar\Classes\Enums\Discounts::getInstance()->discounts;
                break;

            case 'partners':
                $this->title = Lang::get('csatar.csatar::lang.plugin.component.logos.partners.title');
                $this->logos = \Csatar\Csatar\Classes\Enums\Partners::getInstance()->partners;
                break;

            default:
                break;
        }
    }
}
