<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class CategorySubCategoryController extends Controller
{
    public function index(Request $request)
    {
        $subCategories = [];
        if(!empty($request->category_id)){
            $subCategories = SubCategory::getNameIdPairs($request->category_id);
        }

        return response()->json(['sub_categories' => $subCategories]);
    }
}
