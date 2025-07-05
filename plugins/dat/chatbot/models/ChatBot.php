<?php namespace Dat\Chatbot\Models;

use Model;

/**
 * Model
 */
class ChatBot extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var bool timestamps are disabled.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = true;

    /**
     * @var string table in the database used by the model.
     */
    public $table = 'dat_chatbot_';
    public $fillable = ['question', 'answer'];

    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];

}
