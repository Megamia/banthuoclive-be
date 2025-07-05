<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateBetodLivotecIngredientsInstructions extends Migration
{
    public function up()
    {
        Schema::create('betod_livotec_ingredients_instructions', function($table)
        {
            $table->increments('id');
            $table->integer('product_id');
            $table->text('ingredients')->nullable();
            $table->text('instructions')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('betod_livotec_ingredients_instructions');
    }
}
