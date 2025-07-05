<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecProduct22 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_product', function($table)
        {
            $table->boolean('available');
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_product', function($table)
        {
            $table->dropColumn('available');
        });
    }
}
