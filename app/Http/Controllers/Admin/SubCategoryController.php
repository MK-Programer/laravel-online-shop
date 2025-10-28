<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubCategoryRequest;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function index(Request $request)
    {
        $subCategories = SubCategory::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('category', function ($qry) use ($search) {
                        $qry->where('name', 'like', '%' . $search . '%');
                    });
            })
            ->with(['category'])
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.sub-category.list', compact('subCategories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->pluck('name', 'id');
        return view('admin.sub-category.create', compact('categories'));
    }

    public function store(SubCategoryRequest $request)
    {
        $subCategory = new SubCategory();
        $subCategory->category_id = $request->category;
        $subCategory->name = $request->name;
        $subCategory->slug = $request->slug;
        $subCategory->status = $request->status;
        $subCategory->save();

        return response()->json(['message' => 'Sub Category added successfully.']);
    }
}
