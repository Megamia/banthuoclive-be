<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateBetodLivotecProductAttributes extends Migration
{
    public function up()
    {
        Schema::create('betod_livotec_product_attributes', function($table)
        {
            $table->increments('id');
            $table->integer('product_id');
            $table->string('attribute_name')->nullable();
            $table->string('attribute_value')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('betod_livotec_product_attributes');
    }
}
