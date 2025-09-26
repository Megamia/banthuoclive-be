<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecDoctor4 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_doctor', function($table)
        {
            $table->text('short_description')->nullable();
            $table->text('specialty_description')->nullable();
            $table->date('experience_year')->nullable();
            $table->text('work_process')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_doctor', function($table)
        {
            $table->dropColumn('short_description');
            $table->dropColumn('specialty_description');
            $table->dropColumn('experience_year');
            $table->dropColumn('work_process');
        });
    }
}
