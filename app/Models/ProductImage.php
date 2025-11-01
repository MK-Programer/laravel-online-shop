<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $guarded = [];

    public static function imagesFolderLocation()
    {
        return config('app.admin_uploads_folder') . '/product';
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

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getThumb()
    {
        $value = $this->getRawOriginal('image');
        return $value ? url(self::thumbFolderLocation() . '/' . $value) : null;
    }
}
