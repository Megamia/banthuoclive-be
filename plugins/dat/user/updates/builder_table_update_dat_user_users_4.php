<?php namespace Dat\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDatUserUsers4 extends Migration
{
    public function up()
    {
        Schema::table('dat_user_users', function($table)
        {
            $table->text('username')->nullable()->change();
            $table->text('first_name')->nullable()->change();
            $table->text('last_name')->nullable()->change();
            $table->string('email', 255)->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('dat_user_users', function($table)
        {
            $table->text('username')->nullable(false)->change();
            $table->text('first_name')->nullable(false)->change();
            $table->text('last_name')->nullable(false)->change();
            $table->string('email', 255)->nullable(false)->change();
        });
    }
}
