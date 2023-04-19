<?php
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarKnowledgerepositoryMethodologies extends Migration
{
    public function up()
    {
        Schema::table('csatar_knowledgerepository_methodologies', function($table)
        {
            $table->timestamp('deleted_at')->nullable();
            $table->integer('sort_order')->nullable();
            $table->dateTime('version')->nullable();
            $table->integer('association_id')->after('id')->unsigned();
            $table->foreign('association_id', 'methodology_association_foreign')->references('id')->on('csatar_csatar_associations');
        });
    }

    public function down()
    {
        Schema::table('csatar_knowledgerepository_methodologies', function($table)
        {
            $table->dropColumn('deleted_at');
            $table->dropColumn('sort_order');
            $table->dropColumn('version');
            $table->dropForeign('methodology_association_foreign');
            $table->dropColumn('association_id');
        });
    }
}
