<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecSpecialties extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_specialties', function($table)
        {
            $table->integer('doctor_id');
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_specialties', function($table)
        {
            $table->dropColumn('doctor_id');
        });
    }
}
