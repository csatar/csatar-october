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
                'logo' => '/home/partners/mcsszf.png'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.partners.transcarpathianHungarianScoutAssociation'),
                'address' => 'https://cserkesz.com.ua/',
                'logo' => '/home/partners/kmcssz.png'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.partners.hungarianScoutAssociation'),
                'address' => 'https://www.cserkesz.hu/',
                'logo' => '/home/partners/cserkesz-hu.png'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.partners.slovakHungarianScoutAssociation'),
                'address' => 'https://www.szmcs.sk/',
                'logo' => '/home/partners/szmcs.png'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.partners.hungarianScoutAssociationOfVojvodina'),
                'address' => 'https://www.vmcssz.rs/',
                'logo' => '/home/partners/vmcssz.png'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.partners.archdiocesanYouthHeadquarters'),
                'address' => 'https://www.fif.ma/',
                'logo' => '/home/partners/fif-ma.png'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.partners.marySWayTransylvania'),
                'address' => 'http://www.mariaut.ro/',
                'logo' => '/home/partners/via-mariae.png'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.partners.proEducatione'),
                'address' => 'http://proeducatione.ro/',
                'logo' => '/home/partners/pro-educatione.png'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.partners.scoutsOfRomania'),
                'address' => 'http://proeducatione.ro/',
                'logo' => '/home/partners/romanian-scouts.png'
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
