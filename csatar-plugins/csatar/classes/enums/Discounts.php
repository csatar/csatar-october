<?php

namespace Csatar\Csatar\Classes\Enums;

use Lang;

class Discounts
{
    public $discounts;

    // Singleton pattern - Hold the class instance.
    private static $instance = null;
  
    // The constructor is private to prevent initiation with outer code. The expensive process (e.g.,db connection) goes here.
    private function __construct()
    {
        $this->discounts = [
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.discounts.mormotaLand'),
                'address' => 'https://www.mormota.ro/',
                'logo' => '/home/discounts/mormota-land.webp'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.discounts.tiboo'),
                'address' => 'https://www.tiboo.ro/',
                'logo' => '/home/discounts/tiboo.webp'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.discounts.giftyShop'),
                'address' => 'https://rmcssz.ro/www.giftyshop.ro',
                'logo' => '/home/discounts/gifty-shop.webp'
            ],
            [
                'name' => Lang::get('csatar.csatar::lang.plugin.component.logos.discounts.zergeSpecialtyStore'),
                'address' => 'https://zerge-szakbolt-gyergyo.business.site/',
                'logo' => '/home/discounts/zerge-specialty-store.webp'
            ],
        ];
    }
 
    // The object is created from within the class itself only if the class has no instance.
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Discounts();
        }
    
        return self::$instance;
    }
}
