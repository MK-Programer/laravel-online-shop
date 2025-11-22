<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\CustomerAddress;
use App\Models\OrderProduct;
use Illuminate\Http\Request;

use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Auth;
use App\Models\Country;
use App\Models\Order;

class CheckoutController extends Controller
{
    public function index()
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

        $authUser = Auth::user();
        $customerAddress = $authUser->latest_address;
        session()->forget('url.intended');
        return view('customer.checkout', compact('countries', 'customerAddress'));
    }

    public function checkout(CheckoutRequest $request)
    {
        $authUser = Auth::user();

        $customerAddress = new CustomerAddress();
        $customerAddress->user_id = $authUser->id;
        $customerAddress->first_name = $request->first_name;
        $customerAddress->last_name = $request->last_name;
        $customerAddress->email = $request->email;
        $customerAddress->phone = $request->mobile;
        $customerAddress->country_id = $request->country->id;
        $customerAddress->address = $request->address;
        $customerAddress->apartment = $request->apartment;
        $customerAddress->city = $request->city;
        $customerAddress->state = $request->state;
        $customerAddress->zip = $request->zip;
        $customerAddress->notes = $request->order_notes;
        $customerAddress->save();

        if($request->payment_method == 'cod')
        {
            $order = new Order();
            $order->user_id = $authUser->id;

            $order->subtotal = Cart::subtotal();
            $order->tax = $order->subtotal * Cart::tax(); 
            $order->shipping = config('app.shipping_amount');
            $order->coupon_code = null;
            $order->discount = null;
            $order->total = ($order->subtotal + $order->tax + $order->shipping) - ($order->discount);
            
            $order->customer_address_id = $customerAddress->id;
            
            $order->save();

            foreach(Cart::content() as $item)
            {
                $orderProduct = new OrderProduct();
                $orderProduct->order_id = $order->id;
                $orderProduct->product_id = $item->id;
                $orderProduct->qty = $item->qty;
                $orderProduct->price = $item->price;

                $productQtyPrice = $item->qty * $item->price;
                $taxPercentage = config('cart.tax');
                $taxAmount = round($productQtyPrice * $taxPercentage / 100, 2);
                $orderProduct->tax = ['percentage' => $taxPercentage, 'amount' => $taxAmount];
                $orderProduct->total = $productQtyPrice + $taxAmount;
                
                $orderProduct->save();
            }

            Cart::destroy();
            session()->flash('success', 'You have successfully placed your order.');
            return response()->json(['message' => 'Order saved successfully', 'order_id' => $order->id]);
        }
        else if($request->payment_method == 'stripe')
        {

        }
    }

    public function thanks($record)
    {
        session()->flash('success', 'You have successfully placed your order.');
        return view('customer.thanks', compact('record'));
    }
}
