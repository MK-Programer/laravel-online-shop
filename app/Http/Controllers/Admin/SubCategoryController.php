<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubCategoryRequest;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
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
