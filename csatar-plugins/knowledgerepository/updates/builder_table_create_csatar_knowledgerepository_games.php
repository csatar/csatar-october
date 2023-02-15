<?php namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositoryGames extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_games', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('other_tools')->nullable();
            $table->text('link')->nullable();
            $table->string('uploader_csatar_code')->nullable();
            $table->string('approver_csatar_code')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('note')->nullable();

            $table->foreign('uploader_csatar_code', 'games_uploader_code_foreign')->references('ecset_code')->on('csatar_csatar_scouts');
            $table->foreign('approver_csatar_code', 'games_approver_code_foreign')->references('ecset_code')->on('csatar_csatar_scouts');
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_games');
    }
}
