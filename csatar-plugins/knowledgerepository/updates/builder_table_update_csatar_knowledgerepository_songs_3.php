<?php 
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarKnowledgerepositorySongs3 extends Migration
{
    public function up()
    {
        Schema::table('csatar_knowledgerepository_songs', function($table)
        {
            $table->integer('association_id')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('csatar_knowledgerepository_songs', function($table)
        {
            $table->dropColumn('association_id');
        });
    }
}
