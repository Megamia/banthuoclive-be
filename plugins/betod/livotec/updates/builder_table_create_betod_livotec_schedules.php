<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateBetodLivotecSchedules extends Migration
{
    public function up()
    {
        Schema::create('betod_livotec_schedules', function($table)
        {
            $table->increments('id')->unsigned();
            $table->integer('doctor_id');
            $table->date('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('betod_livotec_schedules');
    }
}
