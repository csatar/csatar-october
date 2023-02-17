<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class Migration1077 extends Migration
{
    public function up()
    {
         Schema::table('pollozen_simplegallery_galleries', function($table)
         {
             $table->integer('sort_order')->nullable()->unsigned()->default(0);
         });
    }

    public function down()
    {
        Schema::table('pollozen_simplegallery_galleries', function($table)
         {
             $table->dropColumn('sort_order');
         });
    }
}