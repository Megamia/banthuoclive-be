<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecOrderPositions3 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_order_positions', function($table)
        {
            $table->renameColumn('orders_id', 'order_id');
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_order_positions', function($table)
        {
            $table->renameColumn('order_id', 'orders_id');
        });
    }
}
