<?php namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryMethodologies extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_methodologies', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->integer('timeframe_id')->nullable()->unsigned()->index('timeframe_id');
            $table->integer('methodology_type_id')->nullable()->unsigned()->index('methodology_type_id');
            $table->text('link')->nullable();
            $table->string('other_tools')->nullable();
            $table->string('uploader_csatar_code')->nullable();
            $table->string('approver_csatar_code')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('note')->nullable();

            $table->foreign('methodology_type_id', 'methodologies_methodology_type_id_foreign')->references('id')->on('csatar_knowledgerepository_methodology_types');
            $table->foreign('uploader_csatar_code', 'methodologies_uploader_code_foreign')->references('ecset_code')->on('csatar_csatar_scouts');
            $table->foreign('approver_csatar_code', 'methodologies_approver_code_foreign')->references('ecset_code')->on('csatar_csatar_scouts');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_methodologies');
    }
}
