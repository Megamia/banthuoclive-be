<?php namespace Dat\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDatUserUsers7 extends Migration
{
    public function up()
    {
        Schema::table('dat_user_users', function($table)
        {
            $table->text('api_token')->nullable()->change();
            $table->text('phone')->nullable()->change();
            $table->integer('province')->nullable()->change();
            $table->integer('district')->nullable()->change();
            $table->integer('subdistrict')->nullable()->change();
            $table->text('address')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('dat_user_users', function($table)
        {
            $table->text('api_token')->nullable(false)->change();
            $table->text('phone')->nullable(false)->change();
            $table->integer('province')->nullable(false)->change();
            $table->integer('district')->nullable(false)->change();
            $table->integer('subdistrict')->nullable(false)->change();
            $table->text('address')->nullable(false)->change();
        });
    }
}
