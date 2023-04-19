<?php
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryTools extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_tools', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name')->nullable();
            $table->string('note')->nullable();
            $table->boolean('is_approved')->nullable()->default(false);
            $table->string('approver_csatar_code')->nullable();
            $table->string('proposer_csatar_code')->nullable();

            $table->foreign('approver_csatar_code', 'tools_approved_code_foreign')->references('ecset_code')->on('csatar_csatar_scouts');
            $table->foreign('proposer_csatar_code', 'tools_proposer_code_foreign')->references('ecset_code')->on('csatar_csatar_scouts');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_tools');
    }
}
