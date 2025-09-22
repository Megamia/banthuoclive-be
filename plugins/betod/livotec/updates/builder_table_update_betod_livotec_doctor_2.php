<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecDoctor2 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_doctor', function($table)
        {
            $table->integer('specialties_id')->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_doctor', function($table)
        {
            $table->integer('specialties_id')->default(null)->change();
        });
    }
}
