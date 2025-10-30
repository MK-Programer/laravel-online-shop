<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    public static function getNameIdPairs()
    {
        $brands = self::orderBy('name')->pluck('name', 'id');
        return $brands;
    }
}
