<?php
namespace Betod\Livotec\Models;

use Model;

/**
 * Model
 */
class IngredientsAndInstructions extends Model
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
    public $table = 'betod_livotec_ingredientsandinstructions';


    protected $jsonable = ['ingredients'];

    public $belongsTo = [
        'product' => 'Betod\Livotec\Models\Product',
    ];
    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];

}
