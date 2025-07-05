<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecCategory extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_category', function($table)
        {
            $table->integer('parent_id')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_category', function($table)
        {
            $table->dropColumn('parent_id');
        });
    }
}
