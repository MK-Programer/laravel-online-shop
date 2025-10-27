<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class CategoryController extends Controller
{
    private $tempImagesController,
        $imagesFolderPath,
        $thumbFolderPath;

    public function __construct()
    {
        $this->tempImagesController = new TempImagesController();
        $this->imagesFolderPath = Category::imagesFolder();
        $this->thumbFolderPath = Category::thumbFolder();
    }

    public function index(Request $request)
    {
        $categories = Category::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search . '%');
            })
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.category.list', compact('categories'));
    }

    public function create()
    {
        return view('admin.category.create');
    }

    public function store(Request $request)
    {
        return $this->saveCategory($request);
    }

    private function saveCategory(Request $request, $record = null)
    {
        // 🧩 Common validation
        $rules = [
            'name' => 'required|unique:categories,name' . ($record ? ',' . $record : ''),
            'slug' => 'required|unique:categories,slug' . ($record ? ',' . $record : ''),
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // 🧩 Create or update category
        $category = $record ? Category::find($record) : new Category();
        if ($record && !$category) {
            return response()->json(['error' => 'Category not found.'], 404);
        }

        $category->fill([
            'name' => $request->name,
            'slug' => $request->slug,
            'status' => $request->status,
        ]);
        $category->save();

        // 🧩 Handle image if provided
        if (!empty($request->image_id)) {
            $this->handleCategoryImage($category, $request->image_id);
        }

        $message = $record ? 'Category updated successfully.' : 'Category added successfully.';
        return response()->json(['message' => $message]);
    }

    private function handleCategoryImage(Category $category, $tempImageId)
    {
        $tempImage = TempImage::find($tempImageId);
        if (!$tempImage) return;

        $info = pathinfo($tempImage->path);
        $extension = $info['extension'];
        $newImageName = $category->id . '.' . $extension;

        $tempPath = public_path($this->tempImagesController->getTempFolderName() . '/' . $tempImage->path);
        $targetFolder = public_path($this->imagesFolderPath);
        $thumbFolder = public_path($this->thumbFolderPath);

        if (!File::exists($targetFolder)) {
            File::makeDirectory($targetFolder, 0755, true);
        }
        if (!File::exists($thumbFolder)) {
            File::makeDirectory($thumbFolder, 0755, true);
        }

        $destination = $targetFolder . '/' . $newImageName;
        File::copy($tempPath, $destination);

        // Generate thumbnail
        $manager = new ImageManager(new Driver());
        $img = $manager->read($tempPath);
        // $img->resize(450, 600)->save($thumbFolder . '/' . $newImageName);
        $img->contain(450, 600)->save($thumbFolder . '/' . $newImageName);

        $category->image = $newImageName;
        $category->save();

        $this->tempImagesController->delete(new Request(['image_id' => $tempImageId]));
    }

    public function edit(){}

    public function update(){}

    public function delete() {}
}
