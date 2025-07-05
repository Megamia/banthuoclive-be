<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateBetodLivotecProduct extends Migration
{
    public function up()
    {
        Schema::create('betod_livotec_product', function($table)
        {
            $table->increments('id');
            $table->text('name')->nullable();
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 0)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('betod_livotec_product');
    }
}
