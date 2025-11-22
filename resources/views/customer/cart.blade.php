@extends('customer.layouts.app', ['tab_name' => 'Cart'])

@section('content')
    <section class="section-5 pt-3 pb-3 mb-3 bg-white">
        <div class="container">
            <div class="light-font">
                <ol class="breadcrumb primary-color mb-0">
                    <li class="breadcrumb-item"><a class="white-text" href="{{ route('customer.home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a class="white-text" href="{{ route('customer.shop') }}">Shop</a></li>
                    <li class="breadcrumb-item">Cart</li>
                </ol>
            </div>
        </div>
    </section>

    <section class=" section-9 pt-4">
        <div class="container">
            <div class="row">
                @if ($cart->isNotEmpty())
                    <div class="col-md-8">
                        <div class="table-responsive">
                            <table class="table" id="cart">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th>Remove</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cart as $item)
                                        <tr>
                                            <td class="text-start">
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $item->options->image }}" width="" height="">
                                                    <h2>{{ $item->name }}</h2>
                                                </div>
                                            </td>
                                            <td>{{ config('app.currency') . $item->price  }}</td>
                                            <td>
                                                <div class="input-group quantity mx-auto" style="width: 100px;">
                                                    <div class="input-group-btn">
                                                        <button class="btn btn-sm btn-dark btn-minus p-2 pt-1 pb-1 sub" data-id="{{ $item->rowId }}">
                                                            <i class="fa fa-minus"></i>
                                                        </button>
                                                    </div>
                                                    <input type="text"
                                                        class="form-control form-control-sm  border-0 text-center"
                                                        value="{{ $item->qty }}">
                                                    <div class="input-group-btn">
                                                        <button class="btn btn-sm btn-dark btn-plus p-2 pt-1 pb-1 add" data-id="{{ $item->rowId }}">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                {{ config('app.currency') . number_format($item->qty * $item->price) }}
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-danger" onclick="deleteItem('{{ $item->rowId }}');"><i class="fa fa-times"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card cart-summery">
                            <div class="sub-title">
                                <h2 class="bg-white">Cart Summery</h3>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between pb-2">
                                    <div>Subtotal</div>
                                    <div>{{ config('app.currency') . number_format(Cart::subtotal()) }}</div>
                                </div>
                                <div class="d-flex justify-content-between pb-2">
                                    <div>Tax</div>
                                    <div>{{ '(' . config('cart.tax') . '%)' . config('app.currency') . number_format(Cart::tax())  }}</div>
                                </div>
                                <div class="d-flex justify-content-between pb-2">
                                    <div>Shipping</div>
                                    <div>{{ config('app.currency') . config('app.shipping_amount') }}</div>
                                </div>
                                <div class="d-flex justify-content-between summery-end">
                                    <div>Total</div>
                                    <div>{{ config('app.currency') . number_format(Cart::total() + config('app.shipping_amount')) }}</div>
                                </div>
                                <div class="pt-5">
                                    <a href="{{ route('customer.checkout') }}" class="btn-dark btn btn-block w-100">Proceed to Checkout</a>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="input-group apply-coupan mt-4">
                            <input type="text" placeholder="Coupon Code" class="form-control">
                            <button class="btn btn-dark" type="button" id="button-addon2">Apply Coupon</button>
                        </div> --}}
                    </div>
                @else
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body d-flex justify-content-center align-items-center">
                                <h4>Cart is empty.</h4>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script>
        $('.add').click(function(){
            var qtyElement = $(this).parent().prev();
            var qtyValue = parseInt(qtyElement.val());
            if(qtyValue < 10){
                var newQty = qtyValue + 1;
                qtyElement.val(newQty);
                var rowId = $(this).data('id');
                updateCart(rowId, newQty);
            }
        });

        $('.sub').click(function(){
            var qtyElement = $(this).parent().next();
            var qtyValue = parseInt(qtyElement.val());
            if(qtyValue > 1){ 
                var newQty = qtyValue - 1;
                qtyElement.val(newQty);
                var rowId = $(this).data('id');
                updateCart(rowId, newQty);
            }
        });

        function updateCart(rowId, qty){
            $.ajax({
                url: '{{ route("customer.update-cart") }}',
                type: 'post',
                data: {rowId: rowId, qty: qty},
                dataType: 'json',
                success: function(response){
                    console.log(response);
                    window.location.reload();
                },
                error: function(xhr, status, error){
                    console.error(xhr);
                    const message = xhr.responseJSON?.message || error;
                    alert(message);
                    window.location.reload();
                }
            });
        }

        function deleteItem(rowId){
            if(confirm('Are you sure you want to delete this item?')){
                $.ajax({
                    url: '{{ route("customer.delete-cart-item") }}',
                    type: 'post',
                    data: {rowId: rowId},
                    dataType: 'json',
                    success: function(response){
                        console.log(response);
                        window.location.reload();
                    },
                    error: function(xhr, status, error){
                        console.error(xhr);
                        const message = xhr.responseJSON?.message || error;
                        alert(message);
                        window.location.reload();
                    }
                });
            }

        }
    </script>
@endsection