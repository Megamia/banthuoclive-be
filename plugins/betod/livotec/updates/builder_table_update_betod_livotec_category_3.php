<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecCategory3 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_category', function($table)
        {
            $table->integer('nest_left')->nullable();
            $table->integer('nest_right')->nullable();
            $table->integer('nest_depth')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_category', function($table)
        {
            $table->dropColumn('nest_left');
            $table->dropColumn('nest_right');
            $table->dropColumn('nest_depth');
        });
    }
}
