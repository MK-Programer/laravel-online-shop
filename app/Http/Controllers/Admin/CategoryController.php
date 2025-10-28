<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use Illuminate\Http\Request;
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
                $search = $request->search;
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderByDesc('id')
            ->paginate(10);

        return view('admin.category.list', compact('categories'));
    }

    public function create()
    {
        return view('admin.category.create');
    }

    public function store(CategoryRequest $request)
    {
        return $this->saveCategory($request);
    }

    private function saveCategory(CategoryRequest $request, $record = null)
    {
        // 🧩 Create or update category
        $category = $record ? Category::find($record) : new Category();
        if ($record && !$category)
        {
            return response()->json(['error' => 'Category not found.'], 404);
        }

        $category->name = $request->name;
        $category->slug = $request->slug;
        $category->status = $request->status;
        $category->save();

        // 🧩 Handle image if provided
        if (!empty($request->image_id))
        {
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

        if (!File::exists($targetFolder))
        {
            File::makeDirectory($targetFolder, 0755, true);
        }
        if (!File::exists($thumbFolder))
        {
            File::makeDirectory($thumbFolder, 0755, true);
        }

        $destination = $targetFolder . '/' . $newImageName;
        File::copy($tempPath, $destination);

        // Generate thumbnail
        $manager = new ImageManager(new Driver());
        $img = $manager->read($tempPath);
        // $img->resize(450, 600)->save($thumbFolder . '/' . $newImageName);
        $img->scaleDown(450, 600)
            ->cover(450, 600, 'center')
            ->save($thumbFolder . '/' . $newImageName);

        $category->image = $newImageName;
        $category->save();

        $this->tempImagesController->delete(new Request(['image_id' => $tempImageId]));
    }

    public function edit($record)
    {
        $category = Category::find($record);
        if (empty($category)) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', 'Category not found.');
        }

        return view('admin.category.edit', compact('category'));
    }

    public function update($record, CategoryRequest $request)
    {
        return $this->saveCategory($request, $record);
    }

    public function destroy($record, Request $request)
    {
        $category = Category::find($record);
        if(empty($category))
        {
            return redirect()
                    ->route('admin.categories.index')
                    ->with('error', 'Category not found.');
        }

        $image = $category->getRawOriginal('image');
        File::delete($this->imagesFolderPath.'/'.$image);
        File::delete($this->thumbFolderPath.'/'.$image);
        
        $category->delete();
        return redirect()
                ->route('admin.categories.index')
                ->with('success', 'Category deleted successfully.');
    }
}
