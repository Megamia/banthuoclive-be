<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecAppointment2 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_appointment', function($table)
        {
            $table->integer('user_id')->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_appointment', function($table)
        {
            $table->smallInteger('user_id')->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
        });
    }
}
