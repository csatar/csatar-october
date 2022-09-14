<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateCsatarCsatarGalleryModel extends Migration
{
    public function up()
    {
        Schema::create('csatar_csatar_gallery_model', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('id')->unsigned();
            $table->string('model_type', 255)->nullable();
            $table->integer('model_id');
            $table->integer('gallery_id');
            $table->integer('parent_id')->nullable();
            $table->primary(['id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('csatar_csatar_gallery_model');
    }
}