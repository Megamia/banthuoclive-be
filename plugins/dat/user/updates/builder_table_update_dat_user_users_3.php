<?php namespace Dat\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDatUserUsers3 extends Migration
{
    public function up()
    {
        Schema::table('dat_user_users', function($table)
        {
            $table->text('api_token');
        });
    }
    
    public function down()
    {
        Schema::table('dat_user_users', function($table)
        {
            $table->dropColumn('api_token');
        });
    }
}
