<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecAppointment4 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_appointment', function($table)
        {
            $table->integer('queue_number');
            $table->dropColumn('available');
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_appointment', function($table)
        {
            $table->dropColumn('queue_number');
            $table->boolean('available')->default(0);
        });
    }
}
