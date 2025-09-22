<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecSchedules3 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_schedules', function($table)
        {
            $table->integer('doctor_id')->unsigned()->change();
            $table->string('day_of_week', 0)->nullable()->unsigned(false)->default(null)->comment(null)->change();
            $table->time('start_time')->nullable()->change();
            $table->time('end_time')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_schedules', function($table)
        {
            $table->integer('doctor_id')->unsigned(false)->change();
            $table->date('day_of_week')->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
            $table->time('start_time')->nullable(false)->change();
            $table->time('end_time')->nullable(false)->change();
        });
    }
}
