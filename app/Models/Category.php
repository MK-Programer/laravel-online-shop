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

    public static function imagesFolder()
    {
        return config('app.admin_uploads_folder') . '/category';
    }

    public static function thumbFolder()
    {
        return self::imagesFolder() . '/thumb';
    }

    public function getImageAttribute($value)
    {
        return $value ? url(self::imagesFolder() . '/' . $value) : null;
    }

    public function getThumb()
    {
        $value = $this->getRawOriginal('image');
        return $value ? url(self::thumbFolder() . '/' . $value) : null;
    }

    public static function getNameIdPairs()
    {
        $categories = self::orderBy('name')->pluck('name', 'id');
        return $categories;
    }
}
