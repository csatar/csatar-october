<?php namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateCsatarCsatarGalleryModel extends Migration
{
    public function up()
    {
        Schema::table('csatar_csatar_gallery_model', function($table)
        {
            $table->dropPrimary(['id']);
            $table->dropColumn('id');
            $table->primary(['model_id','gallery_id']);
        });
    }
    
    public function down()
    {
        Schema::table('csatar_csatar_gallery_model', function($table)
        {
            $table->dropPrimary(['model_id','gallery_id']);
            $table->integer('id')->unsigned();
            $table->primary(['id']);
        });
    }
}