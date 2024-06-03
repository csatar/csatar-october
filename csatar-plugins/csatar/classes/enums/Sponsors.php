<?php

namespace Csatar\Csatar\Classes\Enums;

use Lang;

class Sponsors
{
    public $sponsors;

    // Singleton pattern - Hold the class instance.
    private static $instance = null;

    // The constructor is private to prevent initiation with outer code. The expensive process (e.g.,db connection) goes here.
    private function __construct()
    {
        $this->sponsors = [
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.sponsors.hungarianGovernment'),
                'address' => 'https://bgazrt.hu/',
                'logo' => '/home/sponsors/bga.webp',
                'class' => 'main-sponsor'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.sponsors.harghitaCountyCouncil'),
                'address' => 'https://judetulharghita.ro/',
                'logo' => '/home/sponsors/harghita-county-council.webp'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.sponsors.communitasFoundation'),
                'address' => 'https://communitas.ro/',
                'logo' => '/home/sponsors/communitas-foundation.webp'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.sponsors.toyota'),
                'address' => 'https://harghita.toyota.ro',
                'logo' => '/home/sponsors/toyota.webp'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.sponsors.erasmus'),
                'address' => 'https://erasmus-plus.ec.europa.eu',
                'logo' => '/home/sponsors/erasmus.webp'
            ],
        ];
    }

    // The object is created from within the class itself only if the class has no instance.
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Sponsors();
        }

        return self::$instance;
    }

}
