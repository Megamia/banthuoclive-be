<?php namespace Dat\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateDatUserUsers extends Migration
{
    public function up()
    {
        Schema::create('dat_user_users', function($table)
        {
            $table->increments('id')->unsigned();
            $table->text('username');
            $table->text('first_name');
            $table->text('last_name');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('dat_user_users');
    }
}
