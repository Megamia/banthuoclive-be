<?php
namespace Betod\Livotec\Models;

use Model;

/**
 * Model
 */
class AdditionalUser extends Model
{
    use \October\Rain\Database\Traits\Validation;


    /**
     * @var string table in the database used by the model.
     */
    public $table = 'betod_livotec_additional_user';

    protected $fillable = ['user_id', 'phone', 'province', 'district', 'subdistrict', 'address'];
    public $belongsTo = [
        'user' => 'RainLab\User\Models\User'
    ];

    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];

}
