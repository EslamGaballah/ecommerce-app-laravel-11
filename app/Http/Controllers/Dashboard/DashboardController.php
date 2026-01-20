<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //

    public  function index(){
        $totalUsers = User::count();
        $newUsersThisWeek = User::where('created_at', '>=', now()->subDays(7))->count();
        // $activeUsers = User::where('last_login_at', '>=', now()->subDay())->count();

        // 1. إجمالي عدد المنتجات
        $totalProducts = Product::count();
        // 2. المنتجات النشطة فقط (المعروضة للبيع)
        $activeProducts = Product::where('status', 'active')->count();
        // 3. المنتجات التي نفدت من المخزن (Quantity = 0)
        $outOfStock = Product::where('quantity', '<=', 0)->count();

        $totalOrders = Order::count();
        $todayOrders = Order::whereDate('created_at', now()->today())->count();

        // 1. إجمالي المبيعات التاريخي (All-time Revenue)
        $totalRevenue = Order::where('status', 'completed')->sum('total');

        // 2. مبيعات الشهر الحالي (Current Month Revenue)
        $monthlyRevenue = Order::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        // $salesData = Order::where('status', 'completed')
        // ->selectRaw('SUM(total_price) as gross_sales')
        // ->selectRaw('SUM(discount_amount) as total_discounts')
        // ->selectRaw('SUM(tax_amount) as total_taxes')
        // ->first();

        // صافي المبيعات = الإجمالي - الخصومات + الضرائب (أو حسب سياستك المحاسبية)
        // $netSales = ($salesData->gross_sales - $salesData->total_discounts) + $salesData->total_taxes;    

        return view('starter', compact(
            'totalUsers', 
            'newUsersThisWeek', 
            'totalProducts', 
            'activeProducts', 
            'outOfStock', 
            'totalOrders', 
            'todayOrders',
            'totalRevenue', 
            'monthlyRevenue'
            
            ));
    }
}

