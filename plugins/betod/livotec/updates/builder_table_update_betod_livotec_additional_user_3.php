<?php namespace Betod\Livotec\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateBetodLivotecAdditionalUser3 extends Migration
{
    public function up()
    {
        Schema::table('betod_livotec_additional_user', function($table)
        {
            $table->integer('user_id')->unsigned()->change();
        });
    }
    
    public function down()
    {
        Schema::table('betod_livotec_additional_user', function($table)
        {
            $table->integer('user_id')->unsigned(false)->change();
        });
    }
}
