<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class Migration1067 extends Migration
{

    public function up()
    {
        Schema::table('csatar_csatar_districts', function($table)
        {
            $table->string('slug', 255)->nullable();
            $table->smallInteger('status')->nullable()->default(1);
        });

        Schema::table('csatar_csatar_teams', function($table)
        {
            $table->string('slug', 255)->nullable();
        });

        Schema::table('csatar_csatar_troops', function($table)
        {
            $table->string('slug', 255)->nullable();
            $table->smallInteger('status')->nullable()->default(1);
        });

       Schema::table('csatar_csatar_patrols', function($table)
        {
            $table->string('slug', 255)->nullable();
            $table->smallInteger('status')->nullable()->default(1);
            $table->smallInteger('gender')->nullable()->unsigned();
        });
    }

    public function down()
    {
        Schema::table('csatar_csatar_districts', function($table)
        {
            $table->dropColumn('slug');
            $table->dropColumn('status');
        });

        Schema::table('csatar_csatar_teams', function($table)
        {
            $table->dropColumn('slug');
        });

        Schema::table('csatar_csatar_troops', function($table)
        {
            $table->dropColumn('slug');
            $table->dropColumn('status');
        });

        Schema::table('csatar_csatar_patrols', function($table)
        {
            $table->dropColumn('slug');
            $table->dropColumn('status');
            $table->dropColumn('gender');

        });
    }

}
