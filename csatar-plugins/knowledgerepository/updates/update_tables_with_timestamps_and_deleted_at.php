<?php namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UpdateTablesWithTimestampsAndDeletedAt extends Migration
{
    public function up()
    {
        Schema::table('csatar_knowledgerepository_accident_risk_levels', function($table)
        {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('csatar_knowledgerepository_durations', function($table)
        {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('csatar_knowledgerepository_game_development_goals', function($table)
        {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('csatar_knowledgerepository_game_types', function($table)
        {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('csatar_knowledgerepository_headcounts', function($table)
        {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('csatar_knowledgerepository_locations', function($table)
        {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('csatar_knowledgerepository_methodology_types', function($table)
        {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('csatar_knowledgerepository_tools', function($table)
        {
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('csatar_knowledgerepository_games', function($table)
        {
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::table('csatar_knowledgerepository_methodologies', function($table)
        {
            $table->timestamp('deleted_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('csatar_knowledgerepository_accident_risk_levels', function($table)
        {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });

        Schema::table('csatar_knowledgerepository_durations', function($table)
        {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });

        Schema::table('csatar_knowledgerepository_game_development_goals', function($table)
        {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });

        Schema::table('csatar_knowledgerepository_game_types', function($table)
        {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });

        Schema::table('csatar_knowledgerepository_headcounts', function($table)
        {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });

        Schema::table('csatar_knowledgerepository_locations', function($table)
        {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });

        Schema::table('csatar_knowledgerepository_methodology_types', function($table)
        {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });

        Schema::table('csatar_knowledgerepository_tools', function($table)
        {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->dropColumn('deleted_at');
        });

        Schema::table('csatar_knowledgerepository_games', function($table)
        {
            $table->dropColumn('deleted_at');
        });

        Schema::table('csatar_knowledgerepository_methodologies', function($table)
        {
            $table->dropColumn('deleted_at');
        });
    }
}