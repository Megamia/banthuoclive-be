<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecAdditionalUser2 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_additional_user', function($table)
        {
            $table->integer('province')->nullable()->unsigned(false)->default(null)->comment(null)->change();
            $table->integer('district')->nullable()->unsigned(false)->default(null)->comment(null)->change();
            $table->integer('subdistrict')->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_additional_user', function($table)
        {
            $table->string('province', 255)->nullable()->unsigned(false)->default(null)->comment(null)->change();
            $table->string('district', 255)->nullable()->unsigned(false)->default(null)->comment(null)->change();
            $table->string('subdistrict', 255)->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
}
