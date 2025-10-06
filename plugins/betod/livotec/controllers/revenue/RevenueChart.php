<?php
namespace Betod\Livotec\Controllers\Revenue;

use Backend\Classes\Controller;
use Betod\Livotec\Models\Orders;
use Illuminate\Support\Facades\DB;

class RevenueChart extends Controller
{
    public function chart()
    {
        $revenueData = Orders::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(price) as total')
            ->where('status_id', 2)
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->orderBy('month', 'asc')
            ->pluck('total', 'month')
            ->toArray();

        // \Log::error('data: ', $revenueData);

        if ($revenueData) {
            return response()->json($revenueData);
        } else {
            return response()->json(['message' => 'No data']);
        }
    }
}
