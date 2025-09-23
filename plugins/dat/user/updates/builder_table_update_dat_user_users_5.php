<?php namespace Dat\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateDatUserUsers5 extends Migration
{
    public function up()
    {
        Schema::table('dat_user_users', function($table)
        {
            $table->text('phone');
            $table->integer('province');
            $table->integer('district');
            $table->integer('subdistrict');
            $table->text('address');
        });
    }
    
    public function down()
    {
        Schema::table('dat_user_users', function($table)
        {
            $table->dropColumn('phone');
            $table->dropColumn('province');
            $table->dropColumn('district');
            $table->dropColumn('subdistrict');
            $table->dropColumn('address');
        });
    }
}
