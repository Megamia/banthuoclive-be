<?php
namespace Betod\Livotec\Models;

use Model;

/**
 * Model
 */
class Filter extends Model
{
    use \October\Rain\Database\Traits\Validation;


    /**
     * @var string table in the database used by the model.
     */
    public $table = 'betod_livotec_filter';

    public $belongsTo = [
        'category' => ['Betod\Livotec\Models\Category', 'key' => 'category_id']
    ];

    protected $jsonable = [
        'options'
    ];
    /**
     * @var array rules for validation.
     */
    public $rules = [
    ];

}
