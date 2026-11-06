<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\BundleTier;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BundlesController extends Controller
{
    public function index(): View
    {
        return view('dashboard.bundles.index', [
            'bundles' => Bundle::with('tiers')->withCount('products')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.bundles.create', ['bundle' => new Bundle, 'products' => $this->products()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data) {
            $bundle = Bundle::create($data['bundle'] + ['slug' => $this->uniqueSlug($data['bundle']['name_en'])]);

            $this->syncChildren($bundle, $data);
        });

        return redirect()->route('dashboard.bundles.index')->with('success', __('app.created_successfully'));
    }

    public function edit(Bundle $bundle): View
    {
        $bundle->load('tiers', 'products:id');

        return view('dashboard.bundles.edit', ['bundle' => $bundle, 'products' => $this->products()]);
    }

    public function update(Request $request, Bundle $bundle): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($bundle, $data) {
            $bundle->update($data['bundle']);
            $bundle->tiers()->delete();

            $this->syncChildren($bundle, $data);
        });

        return redirect()->route('dashboard.bundles.index')->with('success', __('app.updated_successfully'));
    }

    public function destroy(Bundle $bundle): RedirectResponse
    {
        $bundle->delete();

        return redirect()->route('dashboard.bundles.index')->with('success', __('app.deleted_successfully'));
    }

    private function products()
    {
        return Product::with('images', 'primaryVariation.images', 'variations.images')->active()->latest()->get();
    }

    private function syncChildren(Bundle $bundle, array $data): void
    {
        $bundle->products()->sync($data['products']);
        $bundle->tiers()->createMany($data['tiers']);
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name_ar' => ['required', 'string', 'max:150'],
            'name_en' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'products' => ['required', 'array', 'min:2'],
            'products.*' => ['integer', Rule::exists('products', 'id')],
            'tiers' => ['required', 'array', 'min:1'],
            'tiers.*.min_quantity' => ['required', 'integer', 'min:2', 'max:1000'],
            'tiers.*.discount_type' => ['required', Rule::in([BundleTier::PERCENT, BundleTier::FIXED])],
            'tiers.*.discount_value' => ['required', 'numeric', 'gt:0', 'max:9999999'],
        ]);

        $tiers = collect($validated['tiers'])->values();

        foreach ($tiers as $index => $tier) {
            if ($tier['discount_type'] === BundleTier::PERCENT && $tier['discount_value'] > 100) {
                throw ValidationException::withMessages(["tiers.{$index}.discount_value" => __('app.bundle_percent_too_high')]);
            }
        }

        if ($tiers->pluck('min_quantity')->map(fn ($quantity) => (int) $quantity)->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages(['tiers' => __('app.bundle_tiers_must_differ')]);
        }

        return [
            'bundle' => [
                'name_ar' => $validated['name_ar'],
                'name_en' => $validated['name_en'],
                'description' => $validated['description'] ?? null,
                'starts_at' => $validated['starts_at'] ?? null,
                'ends_at' => $validated['ends_at'] ?? null,
                'is_active' => $request->boolean('is_active'),
                'distinct_products' => $request->boolean('distinct_products'),
            ],
            'products' => $validated['products'],
            'tiers' => $tiers->sortBy('min_quantity')->values()->all(),
        ];
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'bundle';
        $slug = $base;
        $suffix = 2;

        while (Bundle::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
