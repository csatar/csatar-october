<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarAccidentLogRecords extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_accident_log_records', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('accident_date_time')->nullable();
            $table->string('examiner_name')->nullable();
            $table->string('instructors')->nullable();
            $table->string('program_name')->nullable();
            $table->string('program_type')->nullable();
            $table->string('location')->nullable();
            $table->text('activity')->nullable();
            $table->text('reason')->nullable();
            $table->smallInteger('injured_person_age')->default(NULL)->nullable();
            $table->smallInteger('injured_person_gender')->default(NULL)->nullable();
            $table->string('injured_person_name')->nullable();
            $table->text('injury')->nullable();
            $table->smallInteger('injury_severity')->default(NULL)->nullable();
            $table->string('skipped_days_number')->nullable();
            $table->string('tools_used')->nullable();
            $table->string('transport_to_doctor')->nullable();
            $table->string('evacuation')->nullable();
            $table->text('persons_involved_in_care')->nullable();
            $table->text('url')->nullable();
            $table->integer('user_id')->unsigned();
            
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_csatar_accident_log_records');
    }
}
