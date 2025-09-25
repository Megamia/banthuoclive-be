<?php namespace Dat\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDatUserUsers8 extends Migration
{
    public function up()
    {
        Schema::table('dat_user_users', function($table)
        {
            $table->string('subdistrict', 10)->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('dat_user_users', function($table)
        {
            $table->integer('subdistrict')->nullable()->unsigned(false)->default(null)->comment(null)->change();
        });
    }
}
