<?php
namespace Betod\Livotec\Models;

use Model;

/**
 * Model
 */
class Specialties extends Model
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
    public $table = 'betod_livotec_specialties';

    /**
     * @var array rules for validation.
     */

    public $hasMany = [
        'doctor' => 'Betod\Livotec\Models\Doctor',
    ];

    public $rules = [
    ];

}
