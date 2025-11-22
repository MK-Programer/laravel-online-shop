@extends('customer.layouts.app', ['tab_name' => 'Checkout'])

@section('content')
    <section class="section-5 pt-3 pb-3 mb-3 bg-white">
        <div class="container">
            <div class="light-font">
                <ol class="breadcrumb primary-color mb-0">
                    <li class="breadcrumb-item"><a class="white-text" href="#">Home</a></li>
                    <li class="breadcrumb-item"><a class="white-text" href="#">Shop</a></li>
                    <li class="breadcrumb-item">Checkout</li>
                </ol>
            </div>
        </div>
    </section>

    <section class="section-9 pt-4">
        <div class="container">
            <form action="{{ route('customer.process-checkout') }}" method="post" id="order_form" name="order_form">
                <div class="row">
                    <div class="col-md-8">
                        <div class="sub-title">
                            <h2>Shipping Address</h2>
                        </div>
                        <div class="card shadow-lg border-0">
                            <div class="card-body checkout-form">
                                <div class="row">

                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <input type="text" name="first_name" id="first_name" class="form-control"
                                                placeholder="First Name" value="{{ $customerAddress?->first_name }}">
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <input type="text" name="last_name" id="last_name" class="form-control"
                                                placeholder="Last Name" value="{{ $customerAddress?->last_name }}">
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <input type="text" name="email" id="email" class="form-control"
                                                placeholder="Email" value="{{ $customerAddress?->email }}">
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <select name="country" id="country" class="form-control">
                                                <option value="">Select a country</option>
                                                @foreach ($countries as $country)
                                                    <option value="{{ $country->id }}" {{ $customerAddress?->country_id == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <textarea name="address" id="address" cols="30" rows="3" placeholder="Address" class="form-control">{{ $customerAddress?->address }}</textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <input type="text" name="apartment" id="appartment" class="form-control"
                                                placeholder="Apartment, suite, unit, etc. (optional)" value="{{ $customerAddress?->apartment }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <input type="text" name="city" id="city" class="form-control"
                                                placeholder="City" value="{{ $customerAddress?->city }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <input type="text" name="state" id="state" class="form-control"
                                                placeholder="State" value="{{ $customerAddress?->state }}">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <input type="text" name="zip" id="zip" class="form-control"
                                                placeholder="Zip" value="{{ $customerAddress?->zip }}">
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <input type="number" name="mobile" id="mobile" class="form-control"
                                                placeholder="Mobile No." value="{{ $customerAddress?->phone }}">
                                        </div>
                                    </div>


                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <textarea name="order_notes" id="order_notes" cols="30" rows="2" placeholder="Order Notes (optional)"
                                                class="form-control">{{ $customerAddress?->notes }}</textarea>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sub-title">
                            <h2>Order Summery</h3>
                        </div>
                        <div class="card cart-summery">
                            <div class="card-body">
                                @foreach (Cart::content() as $item)
                                    <div class="d-flex justify-content-between pb-2">
                                        <div class="h6">{{ $item->name }} X {{ $item->qty }}</div>
                                        <div class="h6">{{ config('app.currency') . number_format($item->price * $item->qty) }}</div>
                                    </div>
                                @endforeach
                                <div class="d-flex justify-content-between mt-2">
                                    <div class="h6"><strong>Subtotal</strong></div>
                                    <div class="h6"><strong>{{ config('app.currency') . number_format(Cart::subtotal()) }}</strong></div>
                                </div>
                                <div class="d-flex justify-content-between summary-end">
                                    <div class="h6"><strong>Tax</strong></div>
                                    <div class="h6"><strong>{{ '(' . config('cart.tax') . '%)' . config('app.currency') . number_format(Cart::tax())  }}</strong></div>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <div class="h6"><strong>Shipping</strong></div>
                                    <div class="h6"><strong>{{ config('app.currency') . config('app.shipping_amount') }}</strong></div>
                                </div>
                                <div class="d-flex justify-content-between mt-2 summery-end">
                                    <div class="h5"><strong>Total</strong></div>
                                    <div class="h5"><strong>{{ config('app.currency') . number_format(Cart::total() + config('app.shipping_amount')) }}</strong></div>
                                </div>
                            </div>
                        </div>

                        <div class="card payment-form ">
                            <h3 class="card-title h5">Payment Method</h3>
                            <!-- cash on delivery -->
                            <div>
                                <input checked type="radio" name="payment_method" id="cod" value="cod"> 
                                <label for="cod" class="form-check-label">Cash on delivery</label>
                            </div>
                            <!-- stripe payment -->
                            <div>
                                <input type="radio" name="payment_method" id="stripe" value="stripe"> 
                                <label for="stripe" class="form-check-label">Stripe</label>
                            </div>

                            <div class="card-body p-0 d-none" id="card-payment-form">
                                <div class="mb-3">
                                    <label for="card_number" class="mb-2">Card Number</label>
                                    <input type="text" name="card_number" id="card_number"
                                        placeholder="Valid Card Number" class="form-control">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="expiry_date" class="mb-2">Expiry Date</label>
                                        <input type="text" name="expiry_date" id="expiry_date" placeholder="MM/YYYY"
                                            class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="cvv" class="mb-2">CVV Code</label>
                                        <input type="text" name="cvv" id="cvv" placeholder="123"
                                            class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="pt-4">
                                <button type="submit" class="btn-dark btn btn-block w-100">Pay Now</button>
                            </div>
                        </div>
                        <!-- CREDIT CARD FORM ENDS HERE -->
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('js')
    <script>
        $('#cod').click(function(){
            $('#card-payment-form').addClass('d-none');
        });

        $('#stripe').click(function(){
            $('#card-payment-form').removeClass('d-none');
        });      
        
        submitFormUsingAjax(
            'order_form',
            true,
            function(response){
                window.location.href = "{{ url('thanks') }}/" + response.order_id;
            }
        );
    </script>
@endsection
