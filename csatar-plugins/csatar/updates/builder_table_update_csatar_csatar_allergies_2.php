<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarAllergies2 extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_allergies', function($table)
        {
            $table->renameColumn('title', 'name');
        });
        Schema::table('csatar_csatar_scouts_allergies', function($table)
        {
            $table->renameColumn('details', 'comment');
        });
        Schema::table('csatar_csatar_chronic_illnesses', function($table)
        {
            $table->renameColumn('title', 'name');
        });
        Schema::table('csatar_csatar_legal_relationships', function($table)
        {
            $table->renameColumn('title', 'name');
        });
        Schema::table('csatar_csatar_religions', function($table)
        {
            $table->renameColumn('title', 'name');
        });
        Schema::table('csatar_csatar_special_diets', function($table)
        {
            $table->renameColumn('title', 'name');
        });
        Schema::table('csatar_csatar_tshirt_sizes', function($table)
        {
            $table->renameColumn('title', 'name');
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_allergies', function($table)
        {
            $table->renameColumn('name', 'title');
        });
        Schema::table('csatar_csatar_scouts_allergies', function($table)
        {
            $table->renameColumn('comment', 'details');
        });
        Schema::table('csatar_csatar_chronic_illnesses', function($table)
        {
            $table->renameColumn('name', 'title');
        });
        Schema::table('csatar_csatar_legal_relationships', function($table)
        {
            $table->renameColumn('name', 'title');
        });
        Schema::table('csatar_csatar_religions', function($table)
        {
            $table->renameColumn('name', 'title');
        });
        Schema::table('csatar_csatar_special_diets', function($table)
        {
            $table->renameColumn('name', 'title');
        });
        Schema::table('csatar_csatar_tshirt_sizes', function($table)
        {
            $table->renameColumn('name', 'title');
        });
    }
}