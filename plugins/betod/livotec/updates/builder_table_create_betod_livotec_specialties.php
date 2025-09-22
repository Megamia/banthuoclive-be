<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateBetodLivotecSpecialties extends Migration
{
    public function up()
    {
        Schema::create('betod_livotec_specialties', function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('name');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('betod_livotec_specialties');
    }
}
