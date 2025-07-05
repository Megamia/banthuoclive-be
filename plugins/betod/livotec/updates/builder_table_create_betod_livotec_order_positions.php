<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateBetodLivotecOrderPositions extends Migration
{
    public function up()
    {
        Schema::create('betod_livotec_order_positions', function($table)
        {
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('order_id');
            $table->integer('item_id');
            $table->integer('price')->nullable();
            $table->integer('quantity')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('betod_livotec_order_positions');
    }
}
