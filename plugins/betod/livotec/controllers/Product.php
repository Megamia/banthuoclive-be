<?php
namespace Betod\Livotec\Controllers;

use App\Events\ProductUpdated;
use Backend;
use BackendMenu;
use Backend\Classes\Controller;

class Product extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Betod.Livotec', 'main-menu-item', 'side-menu-item');
    }
    public function listExtendQuery($query)
    {
        if ($status = input('filter_status')) {
            switch ($status) {
                case 'out_of_stock':
                    $query->where('stock', 0);
                    break;
                case 'best_seller':
                    $query->where('sold_out', '>', 100); 
                    break;
                case 'in_stock':
                    $query->where('stock', '>', 0);
                    break;
            }
        }
    }
}
