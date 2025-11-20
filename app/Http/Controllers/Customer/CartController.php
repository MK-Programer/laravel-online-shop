<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            Cart::add($product->id, $product->title, 1, $product->price, ['image' => $product->images->first()->getSmallImage()]);
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
        return view('customer.cart', compact('cart'));
    }

    public function updateCart(Request $request)
    {
        $code = 200;
        $message = 'Cart updated successfuly.';

        $rowId = $request->rowId;
        $qty = $request->qty;

        $item = Cart::get($rowId);
        $product = $this->productController->getProduct($item->id, 'id');
        //* check qty available in stock
        if($product->track_qty == 'Yes')
        {
            if($product->qty < $qty)
            {
                $code = 422;
                $message = 'Requested quantity (' . $qty . ') exceeds available stock (' . $product->qty . ').';
            }
            else
            {    
                Cart::update($rowId, $qty);
            }
        } 
        else
        {    
            Cart::update($rowId, $qty);
        }
        return response()->json(['message' => $message], $code);
    } 

    public function deleteItem(Request $request)
    {
        $rowId = $request->rowId;

        $item = Cart::get($rowId);
        if(!$item)
        {
            $code = 422;
            $message = 'Item not found in cart.';
        }
        else
        {
            $code = 200;
            $message = 'Item removed successfully.';
            Cart::remove($rowId);
        }

        return response()->json(['message' => $message], $code);
    }

    public function checkout()
    {
        // if cart is empty redirect to cart page
        if(Cart::count() == 0) 
            return redirect()
                ->route('customer.cart');
        
        // if user is not logged in redirect to login page
        if(Auth::check() == false)
        {
            // authenticate first then move to checkout page
            session()->put('url.intended', route('customer.checkout'));
            return redirect()
                ->route('customer.login');
        } 

        $countries = Country::orderBy('name')->get();

        session()->forget('url.intended');
        return view('customer.checkout', compact('countries'));
    }
}