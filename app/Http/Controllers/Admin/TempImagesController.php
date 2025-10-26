<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class TempImagesController extends Controller
{
    private $tempFolderName;

    public function __construct()
    {
        $this->tempFolderName = config('app.admin_temp_folder');
    }

    public function create(Request $request)
    {
        $images = $request->images;

        if (!is_array($images)) {
            $images = [$images];
        }

        $uploadedImagesIds = [];
        foreach ($images as $image) {
            $folder = $request->folder;
            $folderPath = public_path('/' . $this->tempFolderName . '/' . $folder);
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, 0755, true);
            }

            $extension = $image->getClientOriginalExtension();
            $fileName = time() . '.' . $extension;

            $image->move($folderPath, $fileName);

            $tempImage = new TempImage();
            $tempImage->path = $folder . '/' . $fileName;
            $tempImage->save();

            $uploadedImagesIds[] = $tempImage->id;
        }
        return response()->json(['images_ids' => $uploadedImagesIds, 'message' => 'Image uploaded successfully'], 200);
    }

    public function delete(Request $request)
    {
        $imageId = $request->input('image_id');

        $image = TempImage::find($imageId);
        if ($image && File::exists(public_path( $this->tempFolderName.'/'.$image->path))) {
            File::delete(public_path($this->tempFolderName.'/'.$image->path));
            $image->delete();
        }

        return response()->json(['success' => true]);
    }


    public function getTempFolderName()
    {
        return $this->tempFolderName;
    }
}
