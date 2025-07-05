<?php namespace Dat\Chatbot\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateDatChatbot extends Migration
{
    public function up()
    {
        Schema::create('dat_chatbot_', function($table)
        {
            $table->increments('id');
            $table->string('question');
            $table->text('answer');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('dat_chatbot_');
    }
}
