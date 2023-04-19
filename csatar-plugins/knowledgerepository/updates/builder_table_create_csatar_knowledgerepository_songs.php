<?php 
namespace Csatar\KnowledgeRepository\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarKnowledgerepositorySongs extends Migration
{
    public function up()
    {
        Schema::create('csatar_knowledgerepository_songs', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('song_type')->nullable();
            $table->string('title', 200)->nullable();
            $table->string('author', 200)->nullable();
            $table->text('text')->nullable();
            $table->integer('folk_song_type')->nullable();
            $table->integer('region')->nullable();
            $table->integer('rhythm')->nullable();
            $table->string('link', 250)->nullable();
            $table->string('uploader_csatar_code', 255)->nullable();
            $table->string('approver_csatar_code', 255)->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->string('note', 300)->nullable();
            $table->dateTime('version')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('csatar_knowledgerepository_songs');
    }
}
