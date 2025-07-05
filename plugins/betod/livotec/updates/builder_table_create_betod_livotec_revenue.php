<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateBetodLivotecRevenue extends Migration
{
    public function up()
    {
        Schema::create('betod_livotec_revenue', function($table)
        {
            $table->increments('id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->date('sale_date');
            $table->string('total_revenue', 255);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('betod_livotec_revenue');
    }
}
