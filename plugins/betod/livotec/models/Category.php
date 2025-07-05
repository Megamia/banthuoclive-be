<?php
namespace Betod\Livotec\Models;

use Model;
use October\Rain\Database\Traits\Validation;
use October\Rain\Database\Traits\NestedTree;


/**
 * Model
 */
class Category extends Model
{
    use Validation;
    use NestedTree; // Thêm NestedTree để quản lý category cha-con dạng cây

    /**
     * @var string table in the database used by the model.
     */
    public $table = 'betod_livotec_category';

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'description',
        'property',
        'nest_left',
        'nest_right',
        'nest_depth',
    ];
    
    public $hasMany = [
        'filters' => ['Betod\Livotec\Models\Filter'],
        'products' => ['Betod\Livotec\Models\Product'],
    ];
    public $attachOne = [
        'image' => 'System\Models\File'
    ];

    protected $jsonable = ['property'];
    /**
     * @var array rules for validation.
     */
    public $rules = [
        'name' => 'required',
        'slug' => 'required|unique:betod_livotec_category',
    ];
    /**
     * Scope to get top-level categories only
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

}
