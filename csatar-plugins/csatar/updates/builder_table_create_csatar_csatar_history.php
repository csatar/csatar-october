<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarHistory extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_history', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('fe_user_id')->nullable()->unsigned();
            $table->integer('be_user_id')->nullable()->unsigned();
            $table->string('model_type');
            $table->integer('model_id')->nullable()->unsigned();
            $table->string('related_model_type')->nullable();
            $table->integer('related_model_id')->nullable()->unsigned();
            $table->string('attribute')->nullable()->index();
            $table->string('cast')->nullable();
            $table->text('description')->nullable();
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('ip_address')->nullable();
            
            $table->foreign('fe_user_id')->references('id')->on('users');
            $table->foreign('be_user_id')->references('id')->on('backend_users');
            
            $table->index(['model_type', 'model_id'], 'model_index');
            $table->index(['related_model_type', 'related_model_id'], 'related_model_index');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_csatar_history');
    }
}