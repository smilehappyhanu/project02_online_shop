@extends('front.layouts.app')
@section('content')
<section class="container">
    <div class="col-md-12 text-center py-5">
        @if(Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ Session::get('success')}}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        <h1>Thank you !</h1>
        <p>Your order id is: {{ $id }}</p>
    </div>
</section>


@endsection