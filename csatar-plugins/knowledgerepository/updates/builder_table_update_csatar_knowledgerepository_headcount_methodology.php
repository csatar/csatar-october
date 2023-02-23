<?php namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarKnowledgerepositoryHeadcountMethodology extends Migration
{
    public function up()
    {
        Schema::table('csatar_knowledgerepository_headcount_methodology', function($table)
        {
            $table->renameColumn('headcount_id', 'head_count_id');
        });
    }
    
    public function down()
    {
        Schema::table('csatar_knowledgerepository_headcount_methodology', function($table)
        {
            $table->renameColumn('head_count_id', 'headcount_id');
        });
    }
}