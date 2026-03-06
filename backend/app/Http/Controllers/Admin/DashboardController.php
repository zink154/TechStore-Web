<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30)->startOfDay();
        $paidStatuses = ['processing', 'shipped', 'delivered'];

        return response()->json([
            'success' => true,
            'data' => [
                'total_products' => Product::count(),
                'total_orders' => Order::count(),
                'total_customers' => User::role('customer')->count(),
                'total_revenue' => Order::whereIn('status', $paidStatuses)->sum('total'),
                'recent_orders' => Order::with('user')
                    ->orderByDesc('created_at')
                    ->take(5)
                    ->get(),
                'orders_by_status' => Order::selectRaw('status, count(*) as count')
                    ->groupBy('status')
                    ->pluck('count', 'status'),
                'revenue_trend' => Order::selectRaw('DATE(created_at) as date, SUM(total) as revenue')
                    ->whereIn('status', $paidStatuses)
                    ->where('created_at', '>=', $thirtyDaysAgo)
                    ->groupByRaw('DATE(created_at)')
                    ->orderBy('date')
                    ->get(),
                'orders_trend' => Order::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->where('created_at', '>=', $thirtyDaysAgo)
                    ->groupByRaw('DATE(created_at)')
                    ->orderBy('date')
                    ->get(),
                'low_stock_products' => Product::where('stock', '<', 10)
                    ->where('is_active', true)
                    ->select('id', 'name', 'stock', 'image_url')
                    ->orderBy('stock')
                    ->take(10)
                    ->get(),
                'activity_logs' => ActivityLog::with('user:id,name')
                    ->orderByDesc('created_at')
                    ->take(15)
                    ->get(),
            ],
        ]);
    }
}
