<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecAppointment5 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_appointment', function($table)
        {
            $table->string('status');
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_appointment', function($table)
        {
            $table->dropColumn('status');
        });
    }
}
