<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecSchedules4 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_schedules', function($table)
        {
            $table->integer('doctor_id')->unsigned(false)->change();
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_schedules', function($table)
        {
            $table->integer('doctor_id')->unsigned()->change();
        });
    }
}
