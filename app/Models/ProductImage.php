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

    public function getSmallImage()
    {
        $image = $this->image;
        return $image ? url(self::imagesFolderLocation() . '/' . $this->product_id .  '/small/' . $image) : null;
    }

    public function getLargeImage()
    {
        $image = $this->image;
        return $image ? url(self::imagesFolderLocation() . '/' . $this->product_id . '/large/' . $image) : null;
    }

    public function getThumb()
    {
        $image = $this->image;
        return $image ? url(self::imagesFolderLocation() . '/' . $this->product_id . '/thumb/' . $image) : null;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
