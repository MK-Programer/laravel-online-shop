<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class TempImagesController extends Controller
{
    private $tempFolderName,
        $imageManager;

    public function __construct()
    {
        $this->tempFolderName = config('app.admin_temp_folder', 'admin-temp');
        $this->imageManager = new ImageManager(new Driver());
    }

    public function create(Request $request)
    {
        $images = is_array($request->images) ? $request->images : [$request->images];
        $uploadedImageIds = [];

        foreach ($images as $image) {
            $uploadedImageIds[] = $this->storeTempImage($image, $request->folder);
        }

        return response()->json(['images_id' => $uploadedImageIds, 'message' => 'Images uploaded successfully.']);
    }

    public function delete(Request $request)
    {
        $imagesIds = $request->get('images_id');
        if(!is_array($imagesIds)) $imagesIds = [$imagesIds];

        foreach ($imagesIds as $imageId) {
            $this->deleteTempImage($imageId);
        }

        return response()->json(['success' => true]);
    }

    public function getTempImagePath($tempImage)
    {
        return public_path("{$this->tempFolderName}/{$tempImage->folder_name}/{$tempImage->image_name}");
    }

    public function getTempThumbPath($tempImage)
    {
        return public_path("{$this->tempFolderName}/{$tempImage->folder_name}/thumb/{$tempImage->image_name}");
    }

    /**
     * Store a single uploaded image temporarily and generate a thumbnail.
     */
    private function storeTempImage($image, $folder)
    {
        $folderPath = public_path("{$this->tempFolderName}/{$folder}");
        File::ensureDirectoryExists($folderPath, 0755, true);

        $fileName = uniqid() . '.' . $image->getClientOriginalExtension();
        $image->move($folderPath, $fileName);

        // Save DB record
        $tempImage = new TempImage();
        $tempImage->folder_name = $folder;
        $tempImage->image_name = $fileName;
        $tempImage->save();

        $this->createThumbnail($folderPath, $fileName);

        return $tempImage->id;
    }

    /**
     * Create a thumbnail for the uploaded image.
     */
    private function createThumbnail($folderPath, $fileName)
    {
        $thumbFolderPath = "{$folderPath}/thumb";
        File::ensureDirectoryExists($thumbFolderPath, 0755, true);

        $image = $this->imageManager->read("{$folderPath}/{$fileName}");
        $image
            ->scaleDown(300, 275)
            ->cover(300, 275, 'center')
            ->save("{$thumbFolderPath}/{$fileName}");
    }

    /**
     * Delete a temporary image and its thumbnail.
     */
    private function deleteTempImage($imageId)
    {
        $images = TempImage::where('id', $imageId)->get();
        if (!$images) return;

        foreach($images as $image){
            $imagePath = public_path("{$this->tempFolderName}/{$image->folder_name}/{$image->image_name}");
            if (File::exists($imagePath)) {
                File::delete($imagePath);
                File::delete("{$this->tempFolderName}/{$image->folder_name}/thumb/{$image->image_name}");
                $image->delete();
            }
        }
    }
}
