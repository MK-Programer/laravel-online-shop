<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Support\Facades\File;

class CategoryController extends Controller
{
    private $tempImagesController,
        $imagesFolderPath,
        $thumbFolderPath;

    public function __construct()
    {
        $this->tempImagesController = new TempImagesController();
        $this->imagesFolderPath = Category::imagesFolderPath();
        $this->thumbFolderPath = Category::thumbFolderPath();
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
        if ($record && !$category) {
            return response()->json(['error' => 'Record not found.'], 404);
        }

        $category->name = $request->name;
        $category->slug = $request->slug;
        $category->status = $request->status;
        $category->save();

        // Handle image removal
        if ($request->has('remove_existing_image')) {
            $this->deleteRecordImages($category);
            $category->image = null;
            $category->save();
        }

        // 🧩 Handle image if provided
        if (!empty($request->images_id)) {
            $this->handleCategoryImage($category, $request->images_id);
        }

        $message = $record ? 'Category updated successfully.' : 'Category added successfully.';
        return response()->json(['message' => $message]);
    }

    private function handleCategoryImage(Category $category, $tempImageId)
    {
        $tempImage = TempImage::find($tempImageId);
        if (!$tempImage) {
            return;
        }

        $tempImagePath = $this->tempImagesController->getTempImagePath($tempImage);
        $tempThumbPath = $this->tempImagesController->getTempThumbPath($tempImage);

        $extension = pathinfo($tempImage->image_name, PATHINFO_EXTENSION);
        $newFileName = "{$category->id}.{$extension}";

        File::ensureDirectoryExists($this->imagesFolderPath, 0755, true);

        // Copy main image
        $destinationPath = "{$this->imagesFolderPath}/{$newFileName}";
        File::copy($tempImagePath, $destinationPath);

        File::ensureDirectoryExists($this->thumbFolderPath, 0755, true);

        File::copy($tempThumbPath, "{$this->thumbFolderPath}/{$newFileName}");

        $category->image = $newFileName;
        $category->save();

        $this->tempImagesController->delete(new Request(['images_id' => [$tempImageId]]));
    }

    public function edit($record)
    {
        $category = Category::find($record);
        if (empty($category)) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', 'Record not found.');
        }

        return view('admin.category.edit', compact('category'));
    }

    public function update($record, CategoryRequest $request)
    {
        return $this->saveCategory($request, $record);
    }

    private function deleteRecordImages($category)
    {
        $image = $category->getRawOriginal('image');
        if ($category->image && File::exists($this->imagesFolderPath . '/' . $image)) {
            File::delete($this->imagesFolderPath . '/' . $image);
            File::delete($this->thumbFolderPath . '/' . $image);
        }
    }

    public function destroy($record, Request $request)
    {
        $category = Category::find($record);
        if (empty($category)) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', 'Record not found.');
        }

        $this->deleteRecordImages($category);
        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
