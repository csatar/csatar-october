<?php

namespace Csatar\Csatar\Classes\Enums;

use Lang;

class Partners
{
    public $partners;

    // Singleton pattern - Hold the class instance.
    private static $instance = null;
  
    // The constructor is private to prevent initiation with outer code. The expensive process (e.g.,db connection) goes here.
    private function __construct()
    {
        $this->partners = [
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.partners.forumOfHungarianScoutAssociations'),
                'address' => 'http://mcsszf.org/',
                'logo' => '/home/partners/mcsszf.webp'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.partners.transcarpathianHungarianScoutAssociation'),
                'address' => 'https://cserkesz.com.ua/',
                'logo' => '/home/partners/cserkesz-com-ua.webp'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.partners.hungarianScoutAssociationInExteris'),
                'address' => 'https://kmcssz.org/',
                'logo' => '/home/partners/kmcssz.webp'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.partners.hungarianScoutAssociation'),
                'address' => 'https://www.cserkesz.hu/',
                'logo' => '/home/partners/cserkesz-hu.webp'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.partners.slovakHungarianScoutAssociation'),
                'address' => 'https://www.szmcs.sk/',
                'logo' => '/home/partners/szmcs.webp'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.partners.hungarianScoutAssociationOfVojvodina'),
                'address' => 'https://www.vmcssz.rs/',
                'logo' => '/home/partners/vmcssz.webp'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.partners.archdiocesanYouthHeadquarters'),
                'address' => 'https://www.fif.ma/',
                'logo' => '/home/partners/fif-ma.webp'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.partners.marySWayTransylvania'),
                'address' => 'http://www.mariaut.ro/',
                'logo' => '/home/partners/via-mariae.webp'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.partners.proEducatione'),
                'address' => 'http://proeducatione.ro/',
                'logo' => '/home/partners/pro-educatione.webp'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.partners.scoutsOfRomania'),
                'address' => 'https://scout.ro/',
                'logo' => '/home/partners/romanian-scouts.webp'
            ],
        ];
    }
 
    // The object is created from within the class itself only if the class has no instance.
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Partners();
        }
    
        return self::$instance;
    }
}
