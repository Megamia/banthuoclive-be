<?php namespace Dat\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDatUserUsers extends Migration
{
    public function up()
    {
        Schema::table('dat_user_users', function($table)
        {
            $table->text('password');
        });
    }
    
    public function down()
    {
        Schema::table('dat_user_users', function($table)
        {
            $table->dropColumn('password');
        });
    }
}
