<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateBetodLivotecOrders extends Migration
{
    public function up()
    {
        Schema::create('betod_livotec_orders', function($table)
        {
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('status_id')->nullable();
            $table->text('property')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('betod_livotec_orders');
    }
}
