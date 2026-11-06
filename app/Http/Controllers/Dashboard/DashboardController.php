<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    

    /**
     * Each block is only loaded for staff allowed to see that section.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('dashboard.index', [
            'orderStats' => $user->can('view-order') ? $this->orderStats() : null,
            'productStats' => $user->can('view-product') ? $this->productStats() : null,
            'productReports' => $user->can('view-product') ? $this->productReports() : null,
            'userStats' => $user->can('manage-users') ? $this->userStats() : null,
        ]);
    }

    private function orderStats(): array
    {
        // Completing an order and paying by card both mark it paid, and a refund clears it.
        $paid = Order::where('payment_status', PaymentStatus::PAID);

        return [
            'total' => Order::count(),
            'today' => Order::whereDate('created_at', today())->count(),
            'revenue' => (clone $paid)->sum('total'),
            'monthRevenue' => (clone $paid)
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('total'),
            'recent' => Order::with(['user', 'address'])->latest()->latest('id')->take(6)->get(),
        ];
    }

    private function productStats(): array
    {
        $stockLeft = 'COALESCE(variations_sum_stock, products.stock, 0)';

        return [
            'total' => Product::count(),
            'active' => Product::active()->count(),
            'outOfStock' => Product::active()->outOfStock()->count(),
            'lowStock' => Product::active()
                ->with(['images', 'variations', 'primaryVariation'])
                ->withSum('variations', 'stock')
                ->havingRaw("{$stockLeft} <= ?", [(int) Setting::get('low_stock_threshold')])
                ->orderByRaw($stockLeft)
                ->take(6)
                ->get(),
        ];
    }

    /**
     * The four product reports on the home page.
     */
    private function productReports(): array
    {
        $relations = ['images', 'variations.images', 'primaryVariation.images'];
        $stockLeft = 'COALESCE(variations_sum_stock, products.stock, 0)';
        $days = (int) Setting::get('stagnant_days');

        return [
            'bestSellers' => Product::with($relations)->bestSelling()->limit(6)->get(),
            'highestStock' => Product::with($relations)
                ->active()
                ->withSum('variations', 'stock')
                ->havingRaw("{$stockLeft} > 0")
                ->orderByRaw("{$stockLeft} desc")
                ->limit(6)
                ->get(),
            'stagnant' => Product::with($relations)
                ->active()
                ->stagnant($days)
                ->withSum('variations', 'stock')
                ->orderByRaw("{$stockLeft} desc")
                ->limit(6)
                ->get(),
            'stagnantDays' => $days,
        ];
    }

    private function userStats(): array
    {
        return [
            'total' => User::count(),
            'newThisWeek' => User::where('created_at', '>=', now()->subDays(7))->count(),
        ];
    }
}
