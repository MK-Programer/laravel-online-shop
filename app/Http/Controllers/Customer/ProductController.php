<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $featuredProductsHomeLimit = 8,
        $latestProductsHomeLimit = 8,
        $priceRangesSteps = 4;


    public function getHomeFeaturedProducts()
    {
        return Product::where('status', 1)
            ->where('is_featured', 'Yes')
            ->whereHas('images')
            ->with(['images'])
            ->take($this->featuredProductsHomeLimit)
            ->orderByDesc('id')
            ->orderBy('title')
            ->get();
    }

    public function getHomeLatestProducts()
    {
        return Product::where('status', 1)
            ->whereHas('images')
            ->with(['images'])
            ->take($this->latestProductsHomeLimit)
            ->orderByDesc('id')
            ->orderBy('title')
            ->get();
    }

    public function getMinPrice()
    {
        $min = Product::where('status', 1)->min('price');
        return floor($min);
    }

    public function getMaxPrice()
    {
        $max = Product::where('status', 1)->max('price');
        return ceil($max);
    }

    public function getProducts($categorySlug = null, $subCategorySlug = null, $brandsId = null, $priceMin = null, $priceMax = null, $sortByColumn = 'id', $sortDirection = 'desc')
    {
        return Product::when($categorySlug, function($qry) use($categorySlug){
                $qry->whereHas('category', function($qry) use($categorySlug){
                    $qry->where('slug', $categorySlug);
                });
            })
            ->when($subCategorySlug, function($qry) use($subCategorySlug){
                $qry->whereHas('sub_category', function($qry) use($subCategorySlug){
                    $qry->where('slug', $subCategorySlug);
                });
            })
            ->when($brandsId, function($qry) use($brandsId){
                $qry->whereIn('brand_id', $brandsId);
            })
            ->when(isset($priceMin) && isset($priceMax), function($qry) use($priceMin, $priceMax){
                $qry->where('price', '>=', $priceMin);
                if($priceMax != config('app.price_max'))
                    $qry->where('price', '<=', $priceMax);
            })
            ->where('status', 1)
            ->whereHas('images')
            ->with(['images'])
            ->orderBy($sortByColumn, $sortDirection)
            ->paginate(6);
    }
}
