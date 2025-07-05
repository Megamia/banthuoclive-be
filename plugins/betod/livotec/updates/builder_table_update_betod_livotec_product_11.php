<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecProduct11 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_product', function($table)
        {
            $table->text('attributes')->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_product', function($table)
        {
            $table->string('attributes', 255)->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
}
