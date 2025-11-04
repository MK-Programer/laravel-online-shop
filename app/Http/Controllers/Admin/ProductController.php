<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $productImageController;

    public function __construct()
    {
        $this->productImageController = new ProductImageController();
    }

    public function index(Request $request)
    {
        $products = Product::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;
                $query->where('title', 'like', '%' . $search . '%');
            })
            ->with(['images'])
            ->orderByDesc('id')
            ->paginate(10);
            
        return view('admin.products.list', compact('products'));
    }

    public function create()
    {
        $categories = Category::getNameIdPairs();
        $brands = Brand::getNameIdPairs();

        return view('admin.products.create', compact('categories', 'brands'));
    }

    public function store(ProductRequest $request)
    {
        return $this->saveProduct($request);
    }

    private function saveProduct(ProductRequest $request, $record = null)
    {
        // 🧩 Create or update product
        $product = $record ? Product::find($record) : new Product();
        if($record && !$product){
            return response()->json(['message' => 'Record not found'], 404);
        }

        $product->title = $request->title;
        $product->slug = $request->slug;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->compare_price = $request->compare_price;
        $product->category_id = $request->category;
        $product->sub_category_id = $request->sub_category;
        $product->brand_id = $request->brand;
        $product->is_featured = $request->is_featured;
        $product->sku = $request->sku;
        $product->bar_code = $request->barcode;
        $product->track_qty = $request->track_qty;
        $product->qty = $request->qty;
        $product->status = $request->status;
        $product->save();

        if ($request->has('remove_existing_image')) {
            $this->productImageController->destroy($request->remove_existing_image);
        }

        // 🧩 Handle product images
        if (!empty($request->images_id)) {
            $this->productImageController->saveProductImages($product->id, $request->images_id, $request->images_order);
        }

        // Handle product images order 
        if (!empty($request->images_id) && !empty($request->images_order)) {
            $this->productImageController->reorderRecordImages($product->id, $request->images_id, $request->images_order);
        }

        $message = $record ? 'Product updated successfully.' : 'Product added successfully.';
        return response()->json(['message' => $message]);
    }

    public function edit($record)
    {
        $product = Product::with(['images'])->find($record);
        if(empty($product))
        {
            return redirect()
                ->route('admin.products.index')
                ->with('error', 'Record not found.');
        }
        
        $subCategories = SubCategory::where('category_id', $product->category_id)->pluck('name', 'id');
        $categories = Category::getNameIdPairs();
        $brands = Brand::getNameIdPairs();
        return view('admin.products.edit', compact('product', 'categories', 'subCategories', 'brands'));
    }

    public function update($record, ProductRequest $request)
    {
        return $this->saveProduct($request, $record);
    }
    
    public function destroy($record, Request $request)
    {
        $product = Product::with(['images'])->find($record);
        if(empty($product))
        {
            return redirect()
                ->route('admin.products.index')
                ->with('error', 'Record not found.');
        }

        $this->productImageController->destroy($product->images->pluck('id'));
        $product->delete();
        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Record deleted successfully.');
    }
}
