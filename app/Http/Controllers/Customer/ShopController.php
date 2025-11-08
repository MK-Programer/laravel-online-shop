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

    public function index(Request $request, $categorySlug = null, $subCategorySlug = null)
    {
        $brandsArray = $this->getBrandFilter($request);
        [$priceMin, $priceMax] = $this->getPriceFilter($request);
        [$sortByColumn, $sortDirection, $sort] = $this->getSortFilter($request);

        $categories = $this->categoryController->getCategories();
        $brands = $this->brandController->getBrands();
        $products = $this->productController->getProducts(
            $categorySlug,
            $subCategorySlug,
            $brandsArray,
            $priceMin,
            $priceMax,
            $sortByColumn,
            $sortDirection
        );

        return view('customer.shop', compact(
            'categories',
            'categorySlug',
            'subCategorySlug',
            'brands',
            'brandsArray',
            'priceMin',
            'priceMax',
            'sort',
            'products'
        ));
    }

    private function getBrandFilter(Request $request): array
    {
        return $request->filled('brands') ? explode(',', $request->get('brands')) : [];
    }

    private function getPriceFilter(Request $request): array
    {
        if ($request->filled('price_min') && $request->filled('price_max')) {
            return [$request->get('price_min'), $request->get('price_max')];
        }

        return [
            $this->productController->getMinPrice(),
            $this->productController->getMaxPrice()
        ];
    }

    private function getSortFilter(Request $request): array
    {
        $sort = $request->filled('sort') ? $request->get('sort') : 'latest';

        switch ($sort) {
            case 'price_asc':
                return ['price', 'asc', $sort];
            case 'price_desc':
                return ['price', 'desc', $sort];
            default:
                return ['id', 'desc', 'latest'];
        }
    }
}
