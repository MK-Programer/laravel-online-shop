<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BrandRequest;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $brands = Brand::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.brands.list', compact('brands'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(BrandRequest $request)
    {
        return $this->saveBrand($request);
    }

    private function saveBrand(BrandRequest $request, $record = null)
    {
        // 🧩 Create or update brand
        $brand = $record ? Brand::find($record) : new Brand();
        if($record && !$brand)
        {
            return response()->json(['error' => 'Record not found.'], 404);
        }

        $brand->name = $request->name;
        $brand->slug = $request->slug;
        $brand->status = $request->status;
        $brand->save();

        $message = $record ? 'Brand updated successfully.' : 'Brand added successfully.';        
        return response()->json(['message' => $message]);
    }

    public function edit($record)
    {
        $brand = Brand::find($record);
        if(empty($brand))
        {
            return redirect()
                ->route('admin.brands.index')
                ->with('error', 'Record not found.');
        }

        return view('admin.brands.edit', compact('brand'));
    }

    public function update($record, BrandRequest $request)
    {
        return $this->saveBrand($request, $record);
    }

    public function destroy($record, Request $request)
    {
        $brand = Brand::find($record);
        if(empty($brand))
        {
            return redirect()
                ->route('admin.brands.index')
                ->with('error', 'Record not found.');
        }

        $brand->delete();
        return redirect()
            ->route('admin.brands.index')
            ->with('success', 'Brand deleted successfully.');
    }
}
