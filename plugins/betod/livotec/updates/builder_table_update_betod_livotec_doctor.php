<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecDoctor extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_doctor', function($table)
        {
            $table->integer('specialties_id');
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_doctor', function($table)
        {
            $table->dropColumn('specialties_id');
        });
    }
}
