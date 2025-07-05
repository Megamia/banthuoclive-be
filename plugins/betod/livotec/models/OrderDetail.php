<?php
namespace Betod\Livotec\Models;

use Model;

/**
 * Model
 */
class OrderDetail extends Model
{
    use \October\Rain\Database\Traits\Validation;


    /**
     * @var string table in the database used by the model.
     */
    public $table = 'betod_livotec_order_positions';

    /**
     * @var array rules for validation.
     */
    public $fillable = [
        'id',
        'order_id',
        'product_id',
        'quantity',
        'price',
    ];

    public $belongsTo = [
        'order' => ['Betod\Livotec\Models\Orders', 'key' => 'order_id'],
        'product' => ['Betod\Livotec\Models\Product', 'key' => 'product_id'],
    ];



    public $rules = [
    ];

}
