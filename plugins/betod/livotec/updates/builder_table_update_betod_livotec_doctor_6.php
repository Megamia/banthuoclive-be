<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecDoctor6 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_doctor', function($table)
        {
            $table->string('email', 255);
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_doctor', function($table)
        {
            $table->dropColumn('email');
        });
    }
}
