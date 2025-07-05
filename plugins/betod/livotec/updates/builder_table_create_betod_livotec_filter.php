<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateBetodLivotecFilter extends Migration
{
    public function up()
    {
        Schema::create('betod_livotec_filter', function($table)
        {
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->integer('category_id');
            $table->text('options')->nullable();
            $table->string('type', 255)->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('betod_livotec_filter');
    }
}
