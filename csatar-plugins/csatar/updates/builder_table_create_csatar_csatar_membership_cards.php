<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarMembershipCards extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_membership_cards', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('rfid_tag', 50)->nullable();
            $table->boolean('active')->nullable();
            $table->string('note', 200)->nullable();
            $table->dateTime('issued_date_time')->nullable();
            $table->integer('scout_id')->unsigned();

            $table->foreign('scout_id')->references('id')->on('csatar_csatar_scouts');
        });

    }

    public function down()
    {
        Schema::dropIfExists('csatar_csatar_membership_cards');
    }
}
