@extends('customer.layouts.app', ['tab_name' => 'Thank you'])

@section('content')
    <section class="container">
        <div class="col-md-12 text-center py-5">
            @include('partials.flash')
            <h1>Thank You!</h1>
            <p>Your Order Id is: {{ $record }}</p>
        </div>
    </section>
@endsection