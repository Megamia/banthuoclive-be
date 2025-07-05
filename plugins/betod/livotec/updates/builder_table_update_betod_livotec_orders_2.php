<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecOrders2 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_orders', function($table)
        {
            $table->integer('price')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_orders', function($table)
        {
            $table->dropColumn('price');
        });
    }
}
