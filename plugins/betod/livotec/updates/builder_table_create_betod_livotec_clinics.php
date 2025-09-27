<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateBetodLivotecClinics extends Migration
{
    public function up()
    {
        Schema::create('betod_livotec_clinics', function($table)
        {
            $table->increments('id')->unsigned();
            $table->text('name');
            $table->text('location');
            $table->integer('doctor_id')->unsigned();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('betod_livotec_clinics');
    }
}
