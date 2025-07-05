<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateBetodLivotecAdditionalUser extends Migration
{
    public function up()
    {
        Schema::create('betod_livotec_additional_user', function($table)
        {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('phone')->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('subdistrict')->nullable();
            $table->string('address')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('betod_livotec_additional_user');
    }
}
