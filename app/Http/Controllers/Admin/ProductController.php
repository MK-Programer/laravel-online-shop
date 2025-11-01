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
        $this->imagesFolderPath = Product::imagesFolderPath();
        $this->thumbFolderPath = Product::thumbFolderPath();
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

        // Handle image removal
        // if ($request->has('remove_existing_image')) {
        //     $this->deleteRecordImages($category);
        //     $category->image = null;
        //     $category->save();
        // }

        // 🧩 Handle image if provided
        if (!empty($request->images_id)) {
            $this->saveProductImages($product->id, $request->images_id, $request->images_order);
        }

        return response()->json(['message' => 'Product added successfully.']);
    }

    private function saveProductImages($productId, $tempImagesId, $tempImagesOrder)
    {
        $tempImagesOrder = array_values($tempImagesOrder);
        $imageManager = new ImageManager(new Driver());

        for ($i = 0; $i < count($tempImagesId); $i++)
        {
            $tempImageId = $tempImagesId[$i];
            $tempImageOrder = $tempImagesOrder[$i];

            $tempImage = TempImage::find($tempImageId);
            if (!$tempImage) {
                continue;
            }

            $productImage = new ProductImage();
            $productImage->product_id = $productId;
            $productImage->image = 'NULL';
            $productImage->sort_order = $tempImageOrder;
            $productImage->save();

            $tempImagePath = $this->tempImagesController->getTempImagePath($tempImage);
            $tempThumbPath = $this->tempImagesController->getTempThumbPath($tempImage);

            $extension = pathinfo($tempImage->image_name, PATHINFO_EXTENSION);
            $newFileName = "{$productId}-{$productImage->id}-" . time() . ".{$extension}";

            $productImage->image = $newFileName;
            $productImage->save();

            // Large Image
            File::ensureDirectoryExists($this->imagesFolderPath . '/large', 0755, true);
            $largeDestinationPath = "{$this->imagesFolderPath}/large/{$newFileName}";
            $image = $imageManager->read($tempImagePath);
            $image->scale(width: 1400)
                ->save($largeDestinationPath);

            // Small Image
            File::ensureDirectoryExists($this->imagesFolderPath . '/small', 0755, true);
            $smallDestinationPath = "{$this->imagesFolderPath}/small/{$newFileName}";
            $image = $imageManager->read($tempImagePath);
            $image->cover(300, 300, 'center')
                ->save($smallDestinationPath);

            File::ensureDirectoryExists($this->thumbFolderPath, 0755, true);

            File::copy($tempThumbPath, "{$this->thumbFolderPath}/{$newFileName}");
            
            $this->tempImagesController->delete(new Request(['images_id' => [$tempImageId]]));
        }
    }
}
