<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function getBrands()
    {
        return Brand::whereHas(
                'products', 
                function($qry){
                    $qry->where('status', 1);
            })
            ->where('status', 1)
            ->orderBy('name')
            ->get();
    }
}
