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
        $categories = Category::getNameIdPairs();
        return view('admin.sub-category.create', compact('categories'));
    }

    public function store(SubCategoryRequest $request)
    {
        return $this->saveSubCategory($request);
    }

    private function saveSubCategory(SubCategoryRequest $request, $record = null)
    {
        // 🧩 Create or update sub category
        $subCategory = $record ? SubCategory::find($record) : new SubCategory();
        if ($record && !$subCategory)
        {
            return response()->json(['error' => 'Record not found.'], 404);
        }

        $subCategory->category_id = $request->category;
        $subCategory->name = $request->name;
        $subCategory->slug = $request->slug;
        $subCategory->status = $request->status;
        $subCategory->save();

        $message = $record ? 'Sub Category updated successfully.' : 'Sub Category added successfully.';

        return response()->json(['message' => $message]);
    }

    public function edit($record)
    {
        $subCategory = SubCategory::find($record);
        if(empty($subCategory))
        {
            return redirect()
                ->route('admin.sub-categories.index')
                ->with('error', 'Record not found.');
        }

        $categories = Category::getNameIdPairs();
        return view('admin.sub-category.edit', compact('subCategory', 'categories'));
    }

    public function update($record, SubCategoryRequest $request)
    {
        return $this->saveSubCategory($request, $record);
    }

    public function destroy($record, Request $request)
    {
        $subCategory = SubCategory::find($record);
        if(empty($subCategory))
        {
            return redirect()
                ->route('admin.sub-categories.index')
                ->with('error', 'Record not found.');
        }

        $subCategory->delete();
        return redirect()
            ->route('admin.sub-categories.index')
            ->with('success', 'Sub Category deleted successfully.');
    }
}
