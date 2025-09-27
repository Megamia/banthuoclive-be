<?php
namespace Betod\Livotec\Models;
use Model;
use Betod\Livotec\Models\Clinics;
use Betod\Livotec\Models\Doctor;

/**
 * Model
 */
class Appointment extends Model
{
    use \October\Rain\Database\Traits\Validation;


    /**
     * @var string table in the database used by the model.
     */
    public $table = 'betod_livotec_appointment';

    /**
     * @var array rules for validation.
     */
    protected $fillable = [
        'user_id',
        'doctor_id',
        'meeting_time',
        'queue_number'
    ];
    public $belongsTo = [
        'doctor' => [
            'Betod\Livotec\Models\Doctor',
            'key' => 'doctor_id',
            'otherKey' => 'id'
        ],
        'user' => [
            'Dat\User\Models\User',
            'key' => 'user_id',
            'otherKey' => 'id'
        ]
    ];
    public function clinic()
    {
        return $this->hasOneThrough(
            Clinics::class,
            Doctor::class,
            'id',
            'doctor_id',
            'doctor_id',
            'id'
        );
    }

    public $rules = [
    ];

}
