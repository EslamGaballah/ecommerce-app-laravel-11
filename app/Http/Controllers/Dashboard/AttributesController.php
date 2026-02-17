<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class AttributesController extends Controller
{
    public function index()
    {
        $attributes = Attribute::with('attributeValues')->paginate();
        
        return view('dashboard.attributes.index', compact('attributes'));
    }

    public function create(Attribute $attribute)
    {
        return view('dashboard.attributes.create', compact('attribute'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string' , 'max:10'],
        ]);

        Attribute::create($data);

        return Redirect::route('dashboard.attributes.index')
            ->with('success', 'arrtibute created');
    }


     public function edit (Attribute $attribute)
    {

        return view('dashboard.attributes.edit',compact( 'attribute'));

    }

    public function update (Attribute $attribute, Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string' , 'max:10'],
        ]);
      
            $attribute->update($data);

        return Redirect::route('dashboard.attributes.index')
            ->with('success', 'attributes updated');
    }

    public function destroy (Attribute $attribute)
    {
        // $this-> authorize('update', $attribute_value);

        $attribute->delete();

        return back()->with('success', 'attribute Deleted ');
    }
}
