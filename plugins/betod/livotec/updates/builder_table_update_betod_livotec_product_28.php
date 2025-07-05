<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecProduct28 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_product', function($table)
        {
            $table->integer('sold_out')->default(0)->change();
            $table->integer('stock')->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_product', function($table)
        {
            $table->integer('sold_out')->default(null)->change();
            $table->integer('stock')->default(null)->change();
        });
    }
}
