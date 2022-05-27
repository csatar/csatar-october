<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarTroops extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_troops', function($table)
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
            $table->string('troop_leader_name', 255);
            $table->string('troop_leader_phone', 255);
            $table->string('troop_leader_email', 255);
            $table->integer('team_id')->index('team_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_csatar_troops');
    }
}
