<?php
namespace Csatar\Csatar\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class UpdateTeamsDistrictsAssociationsAddGoogleCalendarId extends Migration
{

    public function up()
    {
        Schema::table('csatar_csatar_teams', function($table)
        {
            $table->string('google_calendar_id')->nullable();
        });

        Schema::table('csatar_csatar_districts', function($table)
        {
            $table->string('google_calendar_id')->nullable();
        });

        Schema::table('csatar_csatar_associations', function($table)
        {
            $table->string('google_calendar_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('csatar_csatar_teams', function($table)
        {
            $table->dropColumn('google_calendar_id');
        });

        Schema::table('csatar_csatar_districts', function($table)
        {
            $table->dropColumn('google_calendar_id');
        });

        Schema::table('csatar_csatar_associations', function($table)
        {
            $table->dropColumn('google_calendar_id');
        });
    }

}
