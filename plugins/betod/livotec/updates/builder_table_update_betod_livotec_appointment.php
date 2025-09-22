<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecAppointment extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_appointment', function($table)
        {
            $table->smallInteger('user_id');
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_appointment', function($table)
        {
            $table->dropColumn('user_id');
        });
    }
}
