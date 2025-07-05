<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecOrders extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_orders', function($table)
        {
            $table->integer('status_id')->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_orders', function($table)
        {
            $table->integer('status_id')->default(null)->change();
        });
    }
}
