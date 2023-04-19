<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UpdateMandateTypesAddIsVkColumn extends Migration
{

    public function up()
    {
        Schema::table('csatar_csatar_mandate_types', function($table)
        {
            $table->smallInteger('is_vk')->unsigned()->nullable();
        });
    }

    public function down()
    {
        Schema::table('csatar_csatar_mandate_types', function($table)
        {
            $table->dropColumn('is_vk');
        });
    }

}
