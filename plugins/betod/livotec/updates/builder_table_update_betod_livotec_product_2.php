<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecProduct2 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_product', function($table)
        {
            $table->integer('price')->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_product', function($table)
        {
            $table->decimal('price', 10, 0)->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
}
