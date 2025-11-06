<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    private $categoriesHomeLimit = 8;

    public function getHomeCategories()
    {
        return Category::whereAll(['status', 'show_in_home'], 1)
            ->whereHas(
                'sub_categories', 
                function($qry){
                    $qry->whereAll(['status', 'show_in_home'], 1);
            })
            ->with([
                'sub_categories' => function($qry){
                    $qry->whereAll(['status', 'show_in_home'], 1);
                }
            ])
            ->whereHas(
                'products', 
                function($qry){
                    $qry->where('status', 1);
            })
            ->withCount([
                'products as active_products_count' => function($qry){
                    $qry->where('status', 1);
                }
            ])
            ->orderByDesc('id')
            ->orderBy('name')
            ->take($this->categoriesHomeLimit)
            ->get();
    }

    public function getCategories()
    {
        return Category::whereHas(
                'products', 
                function($qry){
                    $qry->where('status', 1);
            })
            ->where('status', 1)
            ->with([
                'sub_categories' => function($qry){
                    $qry->where('status', 1);
                }
            ])
            ->orderBy('name')
            ->get();
    }
}
