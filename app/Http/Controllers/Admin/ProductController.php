<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProductController extends Controller
{
    private $tempImagesController,
        $imagesFolderPath,
        $thumbFolderPath;

    public function __construct()
    {
        $this->tempImagesController = new TempImagesController();
        $this->imagesFolderPath = ProductImage::imagesFolderPath();
        $this->thumbFolderPath = ProductImage::thumbFolderPath();
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
        $product = new Product();
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

        // 🧩 Handle product images
        if (!empty($request->images_id)) {
            $this->saveProductImages($product->id, $request->images_id, $request->images_order);
        }

        return response()->json(['message' => 'Product added successfully.']);
    }

    private function saveProductImages($productId, $tempImagesId, $tempImagesOrder)
    {
        $imageManager = new ImageManager(new Driver());
        $tempImagesOrder = array_values($tempImagesOrder);

        foreach ($tempImagesId as $i => $tempImageId) {
            $tempImage = TempImage::find($tempImageId);
            if (!$tempImage) {
                continue;
            }

            $order = $tempImagesOrder[$i];

            // Create record first
            $productImage = new ProductImage();
            $productImage->product_id = $productId;
            $productImage->sort_order = $order;
            $productImage->image = null;
            $productImage->save();

            // Prepare paths
            $tempImagePath = $this->tempImagesController->getTempImagePath($tempImage);
            $tempThumbPath = $this->tempImagesController->getTempThumbPath($tempImage);
            $extension = pathinfo($tempImage->image_name, PATHINFO_EXTENSION);
            $newFileName = "{$productId}-{$productImage->id}-" . time() . ".{$extension}";

            // Update filename in DB
            $productImage->image = $newFileName;
            $productImage->save();

            // Large image (scaled)
            File::ensureDirectoryExists("{$this->imagesFolderPath}/large", 0755, true);
            $largeDestination = "{$this->imagesFolderPath}/large/{$newFileName}";
            $imageManager->read($tempImagePath)
                ->scale(width: 1400)
                ->save($largeDestination);

            // Small image (square cropped)
            File::ensureDirectoryExists("{$this->imagesFolderPath}/small", 0755, true);
            $smallDestination = "{$this->imagesFolderPath}/small/{$newFileName}";
            $imageManager->read($tempImagePath)
                ->cover(300, 300, 'center')
                ->save($smallDestination);

            // Thumbnail
            File::ensureDirectoryExists($this->thumbFolderPath, 0755, true);
            File::copy($tempThumbPath, "{$this->thumbFolderPath}/{$newFileName}");

            // Delete from temp
            $this->tempImagesController->delete(new Request(['images_id' => [$tempImageId]]));
        }
    }
}
