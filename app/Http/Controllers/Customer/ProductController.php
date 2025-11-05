<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $featuredProductsHomeLimit = 8,
        $latestProductsHomeLimit = 8;


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
}
