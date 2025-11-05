<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'slug',
        'image',
        'status',
        'show_in_home',
    ];

    public static function imagesFolderLocation()
    {
        return config('app.admin_folder_name') . '/' . config('app.uploads_folder_name') . '/category';
    }

    public static function imagesFolderPath()
    {
        return public_path(self::imagesFolderLocation());
    }

    public static function thumbFolderLocation()
    {
        return self::imagesFolderLocation() . '/thumb';
    }

    public static function thumbFolderPath()
    {
        return public_path(self::thumbFolderLocation());
    }

    public function getImage()
    {
        $value = $this->image;
        return $value ? url(self::imagesFolderLocation() . '/' . $value) : null;
    }

    public function getThumb()
    {
        $value = $this->image;
        return $value ? url(self::thumbFolderLocation() . '/' . $value) : null;
    }

    public static function getNameIdPairs()
    {
        $categories = self::orderBy('name')->pluck('name', 'id');
        return $categories;
    }

    public function sub_categories()
    {
        return $this->hasMany(SubCategory::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
