<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    private $categoryController,
        $productController;

    public function __construct()
    {
        $this->categoryController = new CategoryController();
        $this->productController = new ProductController();
    }

    public function index()
    {
        $categories = $this->categoryController->getHomeCategories();
        $featuredProducts = $this->productController->getHomeFeaturedProducts();
        $latestProducts = $this->productController->getHomeLatestProducts();

        return view('customer.home', compact('categories', 'featuredProducts', 'latestProducts'));
    }
}
