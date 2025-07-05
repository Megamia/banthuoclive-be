<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecOrders8 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_orders', function($table)
        {
            $table->string('ghn_order_code', 255);
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_orders', function($table)
        {
            $table->dropColumn('ghn_order_code');
        });
    }
}
