<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecOrderPositions extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_order_positions', function($table)
        {
            $table->renameColumn('item_id', 'product_id');
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_order_positions', function($table)
        {
            $table->renameColumn('product_id', 'item_id');
        });
    }
}
