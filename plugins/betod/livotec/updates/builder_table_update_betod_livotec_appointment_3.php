<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecAppointment3 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_appointment', function($table)
        {
            $table->boolean('available')->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_appointment', function($table)
        {
            $table->boolean('available')->default(null)->change();
        });
    }
}
