<?php 
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class Migration1063 extends Migration
{
    public function up()
    {
         Schema::table('pollozen_simplegallery_galleries', function($table)
         {
             $table->boolean('is_public')->nullable();
         });
    }

    public function down()
    {
        Schema::table('pollozen_simplegallery_galleries', function($table)
         {
             $table->dropColumn('is_public');
         });
    }
}
