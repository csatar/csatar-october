<?php namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarKnowledgerepositorySongs extends Migration
{
    public function up()
    {
        Schema::table('csatar_knowledgerepository_songs', function($table)
        {
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('csatar_knowledgerepository_songs', function($table)
        {
            $table->dropColumn('deleted_at');
        });
    }
}
