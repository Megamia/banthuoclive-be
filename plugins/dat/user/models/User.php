<?php
namespace Dat\User\Models;

use Model;

/**
 * Model
 */
class User extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var bool timestamps are disabled.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var string table in the database used by the model.
     */
    public $table = 'dat_user_users';
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'province',
        'district',
        'subdistrict',
        'address',
    ];
    protected $hidden = [
        'password',
        'api_token'
    ];

    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];

}
