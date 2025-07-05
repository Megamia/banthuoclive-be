<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableDeleteBetodLivotecProductAttributes extends Migration
{
    public function up()
    {
        Schema::dropIfExists('betod_livotec_product_attributes');
    }
    
    public function down()
    {
        Schema::create('betod_livotec_product_attributes', function($table)
        {
            $table->increments('id')->unsigned();
            $table->integer('product_id');
            $table->string('attribute_name', 255)->nullable();
            $table->string('attribute_value', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
}
