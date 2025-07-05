<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecIngredientsandinstructions extends Migration
{
    public function up()
    {
        Schema::rename('betod_livotec_ingredients_instructions', 'betod_livotec_ingredientsandinstructions');
    }
    
    public function down()
    {
        Schema::rename('betod_livotec_ingredientsandinstructions', 'betod_livotec_ingredients_instructions');
    }
}
