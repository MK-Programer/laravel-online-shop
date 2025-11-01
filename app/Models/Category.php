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
    ];

    public static function imagesFolderLocation()
    {
        return config('app.admin_uploads_folder') . '/category';
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

    public function getImageAttribute($value)
    {
        return $value ? url(self::imagesFolderLocation() . '/' . $value) : null;
    }

    public function getThumb()
    {
        $value = $this->getRawOriginal('image');
        return $value ? url(self::imagesFolderLocation() . '/' . $value) : null;
    }

    public static function getNameIdPairs()
    {
        $categories = self::orderBy('name')->pluck('name', 'id');
        return $categories;
    }
}
