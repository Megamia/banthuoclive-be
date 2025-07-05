<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecOrders5 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_orders', function($table)
        {
            $table->smallInteger('order_code')->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_orders', function($table)
        {
            $table->string('order_code', 255)->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
}
