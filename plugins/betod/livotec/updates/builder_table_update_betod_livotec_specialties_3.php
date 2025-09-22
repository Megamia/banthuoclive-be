<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecSpecialties3 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_specialties', function($table)
        {
            $table->text('name')->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_specialties', function($table)
        {
            $table->string('name', 255)->nullable(false)->unsigned(false)->default(null)->comment(null)->change();
        });
    }
}
