<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarPatrols extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_patrols', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->string('name', 255);
            $table->string('email', 255)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('facebook_page', 255)->nullable();
            $table->string('patrol_leader_name', 255);
            $table->string('patrol_leader_phone', 255);
            $table->string('patrol_leader_email', 255);
            $table->string('age_group', 255);
            $table->integer('team_id')->index('team_id')->unsigned();
            $table->integer('troop_id')->index('troop_id')->nullable()->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_csatar_patrols');
    }
}
