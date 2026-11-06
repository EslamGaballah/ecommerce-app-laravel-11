<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    /**
     * عرض كل الكوبونات
     */
    public function index(Request $request)
    {
        $coupons = Coupon::query()
            ->when($request->query('code'), fn ($query, $code) => $query->where('code', 'like', "%{$code}%"))
            ->when($request->query('status'), fn ($query, $status) => $query->where('is_active', $status === 'active'))
            ->latest()
            ->paginate(10); // عرض 10 لكل صفحة

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
        Coupon::create($this->validated($request));

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
        $coupon->update($this->validated($request, $coupon));

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

    private function validated(Request $request, ?Coupon $coupon = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:255', Rule::unique('coupons', 'code')->ignore($coupon)],
            'type' => ['required', Rule::in(['fixed', 'percent', 'free_shipping'])],
            'value' => [
                'nullable', 'required_unless:type,free_shipping', 'numeric', 'min:0',
                Rule::when($request->input('type') === 'percent', 'max:100'),
            ],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:today'],
            'is_active' => ['required', 'boolean'],
        ]);

        $data['value'] ??= 0;

        // A coupon stays valid through the whole of its expiry date.
        if (! empty($data['expires_at'])) {
            $data['expires_at'] = Carbon::parse($data['expires_at'])->endOfDay();
        }

        return $data;
    }
}
