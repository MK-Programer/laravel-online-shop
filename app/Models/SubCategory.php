<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'status',
        'show_in_home',
    ];

    public static function getNameIdPairs($categoryId)
    {
        $subCategories = self::when($categoryId, function($query, $categoryId){
                                $query->where('category_id', $categoryId);
                            })
                            ->orderBy('name')->pluck('name', 'id');
        return $subCategories;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
