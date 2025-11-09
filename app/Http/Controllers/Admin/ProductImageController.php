<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TempImage;
use App\Models\ProductImage;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Log;

class ProductImageController extends Controller
{
    private $tempImagesController,
        $imagesFolderPath;

    public function __construct()
    {
        $this->tempImagesController = new TempImagesController();
        $this->imagesFolderPath = ProductImage::imagesFolderPath();
    }

    public function saveProductImages($productId, $tempImagesId, $tempImagesOrder)
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
            $productImage->image = 'NULL';
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
            File::ensureDirectoryExists("{$this->imagesFolderPath}/{$productId}/large", 0755, true);
            $largeDestination = "{$this->imagesFolderPath}/{$productId}/large/{$newFileName}";
            $imageManager->read($tempImagePath)
                ->resizeDown(1000, 1000)
                ->save($largeDestination);

            // Small image (square cropped)
            File::ensureDirectoryExists("{$this->imagesFolderPath}/{$productId}/small", 0755, true);
            $smallDestination = "{$this->imagesFolderPath}/{$productId}/small/{$newFileName}";
            $imageManager->read($tempImagePath)
                ->cover(300, 300, 'center')
                ->save($smallDestination);

            // Thumbnail
            File::ensureDirectoryExists("{$this->imagesFolderPath}/{$productId}/thumb", 0755, true);
            File::copy($tempThumbPath, "{$this->imagesFolderPath}/{$productId}/thumb/{$newFileName}");

            // Delete from temp
            $this->tempImagesController->delete(new Request(['images_id' => [$tempImageId]]));
        }
    }

    public function destroy($imagesId)
    {
        $productImages = ProductImage::whereIn('id', $imagesId)->get();
        foreach($productImages as $productImage)
        {
            $productId = $productImage->product_id;
            $baseImagesFolderPath = $this->imagesFolderPath . '/' . $productId . '/';
            $image = $productImage->image;
            if (File::exists($baseImagesFolderPath . 'thumb/' . $image))
                File::delete($baseImagesFolderPath . 'thumb/' . $image);

            if (File::exists($baseImagesFolderPath . 'large/' . $image))
                File::delete($baseImagesFolderPath . 'large/' . $image);
            
            if (File::exists($baseImagesFolderPath . 'small/' . $image))
                File::delete($baseImagesFolderPath . 'small/' . $image);
            
            $productImage->delete();
        }
    }

    public function reorderRecordImages($productId, $imagesId, $ordersId)
    {
        $ordersId = array_values($ordersId ?? []);

        foreach ($imagesId as $i => $id) {
            $order = $ordersId[$i];

            ProductImage::where('product_id', $productId)
                ->where('id', $id)
                ->update(['sort_order' => $order]);
        }
    }

}
