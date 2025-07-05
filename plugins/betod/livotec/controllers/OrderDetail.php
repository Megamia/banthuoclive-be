<?php namespace Betod\Livotec\Controllers;

use Backend;
use BackendMenu;
use Backend\Classes\Controller;

class OrderDetail extends Controller
{
    public $implement = [
        \Backend\Behaviors\ListController::class
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();
    }

}
