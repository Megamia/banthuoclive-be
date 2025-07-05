<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecProduct3 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_product', function($table)
        {
            $table->string('name')->nullable()->unsigned(false)->default(null)->comment(null)->change();
            $table->string('slug')->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_product', function($table)
        {
            $table->text('name')->nullable()->unsigned(false)->default(null)->comment(null)->change();
            $table->text('slug')->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
}
