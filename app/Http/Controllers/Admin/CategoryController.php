<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Support\Facades\File;

class CategoryController extends Controller
{
    private $tempImagesController,
        $imagesFolderPath = 'admin-uploads/category';

    public function __construct()
    {
        $this->tempImagesController = new TempImagesController();
    }

    public function index(Request $request)
    {
        $categories = Category::latest();

        if ($request->has('search') && !empty($request->search)) {
            $categories = $categories->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = $categories->paginate(10);
        return view('admin.category.list', compact('categories'));
    }

    public function create()
    {
        return view('admin.category.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|unique:categories',
                'slug' => 'required|unique:categories',
            ]
        );

        if ($validator->passes()) {
            $category = new Category();
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->save();

            //* Save image
            if (!empty($request->image_id)) 
            {
                $tempImage = TempImage::find($request->image_id);
            
                $info = pathinfo($tempImage->path);
                // $folder = $info['dirname'];
                // $imageName = $info['filename'];
                $extension = $info['extension'];
                
                $newImageName = $category->id.'.'.$extension;
                $sPath = public_path($this->tempImagesController->getTempFolderName().'/'.$tempImage->path);
                $folderPath = public_path($this->imagesFolderPath);
                if(!File::exists($this->imagesFolderPath))
                {
                    File::makeDirectory($folderPath, 0755, true);
                }
                $dPath = public_path($this->imagesFolderPath.'/'.$newImageName);
                File::copy($sPath, $dPath);
                
                $category->image = $newImageName;
                $category->save();
            }

            return response()->json(['message' => 'Category added successfully.']);
        } else {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    }

    public function edit() {}

    public function update() {}

    public function delete() {}
}
