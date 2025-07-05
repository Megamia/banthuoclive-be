<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecOrders4 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_orders', function($table)
        {
            $table->string('order_code')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_orders', function($table)
        {
            $table->dropColumn('order_code');
        });
    }
}
