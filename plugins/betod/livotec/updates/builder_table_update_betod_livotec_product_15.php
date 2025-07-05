<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecProduct15 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_product', function($table)
        {
            $table->dropColumn('attributes');
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_product', function($table)
        {
            $table->string('attributes', 255)->nullable();
        });
    }
}
