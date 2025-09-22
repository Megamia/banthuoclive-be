<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateBetodLivotecAppointment extends Migration
{
    public function up()
    {
        Schema::create('betod_livotec_appointment', function($table)
        {
            $table->increments('id')->unsigned();
            $table->boolean('available');
            $table->integer('doctor_id');
            $table->dateTime('meeting_time');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('betod_livotec_appointment');
    }
}
