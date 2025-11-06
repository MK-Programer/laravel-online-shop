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

    public function getPriceRanges()
    {
        $min = Product::where('status', 1)->min('price'); // 22.73
        $max = Product::where('status', 1)->max('price'); // 488.23

        if($min == null || $max == null) return []; // no products

        $interval = ceil(($max - $min) / $this->priceRangesSteps); //116

        $ranges = [];

        for($i = 0; $i < $this->priceRangesSteps; $i++)
        {
            $from = $min + ($i * $interval);
            $to = $from + $interval - 1;

            $count = Product::where('status', 1)
                ->whereBetween('price', [$from, $to])
                ->count();

            $ranges[] = [
                'label' => config('app.currency')." {$from} - {$to}",
                'count' => $count,
                'min' => $from,
                'max' => $to,
            ];
        }
        return $ranges;
    }

    public function getProducts()
    {
        return Product::where('status', 1)
            ->orderByDesc('id')
            ->get();
    }
}
