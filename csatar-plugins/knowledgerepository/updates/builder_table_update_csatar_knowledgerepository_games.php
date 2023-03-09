<?php namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarKnowledgerepositoryGames extends Migration
{
    public function up()
    {
        Schema::table('csatar_knowledgerepository_games', function($table)
        {
            $table->integer('association_id')->after('id')->unsigned();
            $table->foreign('association_id', 'game_association_foreign')->references('id')->on('csatar_csatar_associations');
        });
    }
    
    public function down()
    {
        Schema::table('csatar_knowledgerepository_games', function($table)
        {
            $table->dropForeign('game_association_foreign');
            $table->dropColumn('association_id');
        });
    }
}