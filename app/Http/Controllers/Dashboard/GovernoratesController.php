<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Governorate;
use Illuminate\Http\Request;

class GovernoratesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $governorates = Governorate::latest()->paginate(10);

        return view('dashboard.governorates.index', compact('governorates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         return view('dashboard.governorates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'shipping_price' => 'required|numeric|min:0',
            'delivery_days'  => 'required|integer|min:1',
            'is_active'      => 'nullable|boolean',
        ]);

        Governorate::create([
            'name'           => $request->name,
            'shipping_price' => $request->shipping_price,
            'delivery_days'  => $request->delivery_days,
            'is_active'      => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('dashboard.governorates.index')
            ->with('success', 'تم إضافة المحافظة بنجاح');
    
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('dashboard.governorates.edit', compact('governorate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Governorate $governorate)
    {
          $request->validate([
            'name'           => 'required|string|max:255',
            'shipping_price' => 'required|numeric|min:0',
            'delivery_days'  => 'required|integer|min:1',
            'is_active'      => 'nullable|boolean',
        ]);

        $governorate->update([
            'name'           => $request->name,
            'shipping_price' => $request->shipping_price,
            'delivery_days'  => $request->delivery_days,
            'is_active'      => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('dashboard.governorates.index')
            ->with('success', 'تم تحديث المحافظة بنجاح');
    
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Governorate $governorate)
    {
         $governorate->delete();

        return redirect()
            ->route('dashboard.governorates.index')
            ->with('success', 'تم حذف المحافظة');
    }

     public function toggleStatus(Governorate $governorate)
    {
        $governorate->update([
            'is_active' => ! $governorate->is_active
        ]);

        return response()->json([
            'status' => true,
            'is_active' => $governorate->is_active
        ]);
    }
}
