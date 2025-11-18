<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private $productController;

    public function __construct()
    {
        $this->productController = new ProductController();
    }

    public function addToCart(Request $request)
    {
        $product = $this->productController->getProduct( $request->id, 'id');
        if(!$product) return response()->json(['message' => 'Product not found.'], 404);
        
        $item = $this->searchCartForProduct($request->id);
        if($item) //* reject [already exist]
        {
            // Cart::update($item->rowId, $item->qty++);
            $code = 409;
            $message = $item->name . ' already exist in cart.';
        }
        else //* add
        {
            # Cart::add(); id, name, qty, price, options, tax-rate
            Cart::add($product->id, $product->title, 1, $product->price, ['image' => $product->images->first()->getSmallImage()], 14);
            $code = 200;
            $message = $product->title . ' added to cart.';
        }
        return response()->json(['message' => $message], status: $code);
    }

    private function searchCartForProduct($id)
    {
        foreach(Cart::content() as $item)
        {
            if($item->id == $id) return $item;
        }
        
        return null;
    }

    public function index()
    {
        $cart = Cart::content();
        return view('customer.cart.index', compact('cart'));
    }

    

    
}
