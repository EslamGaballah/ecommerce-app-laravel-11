<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class AttributeValueController extends Controller
{
    public function index()
    {
        $attribute_values = AttributeValue::with('attribute')->get();
        
        return view('dashboard.attribute_values.index', compact('attribute_values'));
    }

    public function create(AttributeValue $attribute_value)
    {

        $attributes = Attribute::all();

        return view('dashboard.attribute_values.create',compact('attribute_value', 'attributes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'attribute_id' => 'required|exists:attributes,id',
            'value' => ['required', 'string' ],
        ]);

        // 
        $valuesArray = preg_split('/[,\،\r\n]+/u', $request->value);

        foreach ($valuesArray as $value) {
        $trimmedValue = trim($value);  

        if (!empty($trimmedValue)) {
            AttributeValue::firstOrCreate([
                'attribute_id' => $request->attribute_id,
                'value' => $trimmedValue,
            ]);
        }
    }

        // AttributeValue::create($data);

        return Redirect::route('dashboard.attribute_values.index')
            ->with('success', 'attribute_values created');
    }

    public function edit (AttributeValue $attribute_value)
    {

            $attributes = Attribute::all();

        // $this-> authorize('edit-catggories', $category);
        // $parents = Category::where('id', '<>', $category->id)
        //     ->where(function($query) use($category){
        //     $query->whereNull('parent_id')
        //             ->orWhere('parent_id', '<>', $category->id);
        //     })->get();

        return view('dashboard.attribute_values.edit',compact('attribute_value', 'attributes'));

    }

    public function update (AttributeValue $attribute_value, Request $request)
    {
        $request->validate([
            'attribute_id' => 'required|exists:attributes,id',
            'value' => ['required', 'string', 'max:20' ],
        ]);
      
            $attribute_value->updateOrCreate([
                'attribute_id' => $request->attribute_id,
                'value' => $request->value,
            ]);

        return Redirect::route('dashboard.attribute_values.index')
            ->with('success', 'attribute_values created');
    }

    public function destroy (AttributeValue $attribute_value)
    {
        // $this-> authorize('update', $attribute_value);

        $attribute_value->delete();

        return back()->with('success', 'attribute Value Deleted ');

    }
}
