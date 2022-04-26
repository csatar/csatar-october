<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarSpecialDiets extends Migration
{
    public function up()
    {
        Schema::rename('csatar_csatar_special_diet', 'csatar_csatar_special_diets');
    }
    
    public function down()
    {
        Schema::rename('csatar_csatar_special_diets', 'csatar_csatar_special_diet');
    }
}
