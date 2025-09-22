<?php
namespace Betod\Livotec\Models;

use Model;

/**
 * Model
 */
class Doctor extends Model
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
    public $table = 'betod_livotec_doctor';

    /**
     * @var array rules for validation.
     */
    public $belongsTo = [
        'specialties' => 'Betod\Livotec\Models\Specialties',
    ];
    public $hasMany = [
        'schedules' => [
            'Betod\Livotec\Models\Schedules',
            'key' => 'doctor_id',
            'otherKey' => 'id'
        ]
    ];

    public $rules = [
    ];

}
