<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'total_products' => Product::count(),
                'total_orders' => Order::count(),
                'total_customers' => User::role('customer')->count(),
                'total_revenue' => Order::whereIn('status', ['processing', 'shipped', 'delivered'])->sum('total'),
                'recent_orders' => Order::with('user')
                    ->orderByDesc('created_at')
                    ->take(5)
                    ->get(),
                'orders_by_status' => Order::selectRaw('status, count(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
            ],
        ]);
    }
}
