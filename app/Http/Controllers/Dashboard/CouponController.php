<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /**
     * عرض كل الكوبونات
     */
    public function index()
    {
        $coupons = Coupon::latest()->paginate(10); // عرض 10 لكل صفحة
        return view('dashboard.coupons.index', compact('coupons'));
    }

    /**
     * صفحة إضافة كوبون جديد
     */
    public function create()
    {
        return view('dashboard.coupons.create');
    }

    /**
     * حفظ كوبون جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_date' => 'nullable|date|after_or_equal:today',
            'active' => 'required|boolean'
        ]);

        Coupon::create($request->all());

        return redirect()->route('dashboard.coupons.index')
            ->with('success', 'تم إضافة الكوبون بنجاح');
    }

    /**
     * صفحة تعديل كوبون
     */
    public function edit(Coupon $coupon)
    {
        return view('dashboard.coupons.edit', compact('coupon'));
    }

    /**
     * تحديث بيانات الكوبون
     */
    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'code' => 'required|string|unique:coupons,code,' . $coupon->id,
            'type' => 'required|in:fixed,percent',
            'value' => 'required|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after_or_equal:today',
            'active' => 'required|boolean'
        ]);

        $coupon->update($request->all());

        return redirect()->route('dashboard.coupons.index')
            ->with('success', 'تم تحديث الكوبون بنجاح');
    }

    /**
     * حذف كوبون
     */
    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return redirect()->route('dashboard.coupons.index')
            ->with('success', 'تم حذف الكوبون بنجاح');
    }
}
