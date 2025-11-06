<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    private $categoryController,
        $brandController,
        $productController;

    public function __construct()
    {
        $this->categoryController = new CategoryController();
        $this->brandController = new BrandController();
        $this->productController = new ProductController();
    }

    public function index()
    {
        $categories = $this->categoryController->getCategories();
        $brands = $this->brandController->getBrands();
        $priceRanges = $this->productController->getPriceRanges();
        $products = $this->productController->getProducts();
        return view('customer.shop', compact('categories', 'brands', 'priceRanges', 'products'));
    }
}
