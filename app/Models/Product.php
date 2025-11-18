<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'related_products' => 'array'
    ];

    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = ucfirst($value);
    }

    public function setSkuAttribute($value)
    {
        $this->attributes['sku'] = strtoupper($value);
    }

    public function formattedPrice()
    {
        return config('app.currency') . $this->price;
    }

    public function formattedComparePrice()
    {
        return config('app.currency') . $this->compare_price;
    }

    public function mapRelatedProducts($onlyWithImage = false, $includeImages = false, $allowedStatuses = [1, 0])
    {
        if(!is_array($allowedStatuses)) $allowedStatuses = [$allowedStatuses];

        return Product::whereIn('id', $this->related_products ?? [])
                ->when($onlyWithImage, function($query) {
                    $query->whereHas('images');
                })
                ->when($includeImages, function($query) {
                    $query->with('images');
                })
                ->whereIn('status', $allowedStatuses)
                ->get();
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function sub_category()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
}
