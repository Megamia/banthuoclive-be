<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecCategory2 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_category', function($table)
        {
            $table->string('description')->nullable();
            $table->text('property')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_category', function($table)
        {
            $table->dropColumn('description');
            $table->dropColumn('property');
        });
    }
}
