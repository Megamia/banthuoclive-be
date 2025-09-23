<?php namespace Dat\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDatUserUsers2 extends Migration
{
    public function up()
    {
        Schema::table('dat_user_users', function($table)
        {
            $table->string('email');
        });
    }
    
    public function down()
    {
        Schema::table('dat_user_users', function($table)
        {
            $table->dropColumn('email');
        });
    }
}
