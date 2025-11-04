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
        return config('app.admin_folder_name') . '/' . config('app.uploads_folder_name') . '/product';
    }

    public static function imagesFolderPath()
    {
        return public_path(self::imagesFolderLocation());
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getThumb()
    {
        $value = $this->getRawOriginal('image');
        return $value ? url(self::imagesFolderLocation() . '/' . $this->product_id . '/thumb/' . $value) : null;
    }
}
