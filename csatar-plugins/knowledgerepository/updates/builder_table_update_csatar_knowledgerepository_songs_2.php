<?php
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarKnowledgerepositorySongs2 extends Migration
{
    public function up()
    {
        Schema::table('csatar_knowledgerepository_songs', function($table)
        {
            $table->integer('song_type_id')->nullable();
            $table->integer('folk_song_type_id')->nullable();
            $table->integer('region_id')->nullable();
            $table->integer('rhythm_id')->nullable();
            $table->dropColumn('song_type');
            $table->dropColumn('folk_song_type');
            $table->dropColumn('region');
            $table->dropColumn('rhythm');
        });
    }

    public function down()
    {
        Schema::table('csatar_knowledgerepository_songs', function($table)
        {
            $table->dropColumn('song_type_id');
            $table->dropColumn('folk_song_type_id');
            $table->dropColumn('region_id');
            $table->dropColumn('rhythm_id');
            $table->integer('song_type')->nullable();
            $table->integer('folk_song_type')->nullable();
            $table->integer('region')->nullable();
            $table->integer('rhythm')->nullable();
        });
    }
}
