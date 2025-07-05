<?php

namespace Betod\Livotec\Controllers\Product;

use Backend\Classes\Controller;

class filterProduct extends Controller
{
    public function listExtendQuery($query)
    {
        \Log::info('Đang lọc theo trạng thái: ' . input('filter_status'));

        if ($status = input('filter_status')) {
            switch ($status) {
                case 'out_of_stock':
                    $query->where('stock', '=<', 0);
                    break;
                case 'best_seller':
                    $query->where('sold_out', '>', 100);
                    $query->orderBy('sold_out', 'desc');
                    break;
                case 'in_stock':
                    $query->where('stock', '>', 0);
                    break;
            }
        }
    }

}