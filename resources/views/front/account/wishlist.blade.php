@extends('front.layouts.app')
@section('content')
    <section class="section-5 pt-3 pb-3 mb-3 bg-white">
        <div class="container">
            <div class="light-font">
                <ol class="breadcrumb primary-color mb-0">
                    <li class="breadcrumb-item"><a class="white-text" href="{{ route('account.profile') }}">My Account</a></li>
                    <li class="breadcrumb-item">Wishlist</li>
                </ol>
            </div>
        </div>
    </section>

    <section class=" section-11 ">
        <div class="container  mt-5">
            <div class="row">
                <div class="col-md-3">
                   @include('front.account.common.sidebar')
                </div>
                <div class="col-md-9">
                    <div class="card">
                        @include('front.message')
                        <div class="card-header">
                            <h2 class="h5 mb-0 pt-2 pb-2">My Wishlists</h2>
                        </div>
                        <div class="card-body p-4">                      
                            @if($wishlists->isNotEmpty())
                            @foreach($wishlists as $item)
                                @php
                                    $productInfo = getProductInfo($item->product_id);
                                    $productImage = getProductImage($item->product_id);
                                @endphp

                                <div class="d-sm-flex justify-content-between mt-lg-4 mb-4 pb-3 pb-sm-2 border-bottom">
                                    <div class="d-block d-sm-flex align-items-start text-center text-sm-start">
                                        <a class="d-block flex-shrink-0 mx-auto me-sm-4" href="{{route('front.product',$productInfo->slug)}}" style="width: 10rem;">
                                            @if(!empty($productImage))
                                            <img src="{{asset('uploads/product/small/'.$productImage->image)}}" alt="Product">
                                            @else
                                            <img src="{{asset('admin-assets/img/default-150x150.png')}}" alt="Product">
                                            @endif
                                        </a>
                                        <div class="pt-2">
                                            <h3 class="product-title fs-base mb-2"><a href="{{route('front.product',$productInfo->slug)}}">{{$productInfo->title}}</a></h3>                                        
                                            <div class="fs-lg text-accent pt-2">{{number_format($productInfo->price)}}
                                                @if($productInfo->compare_price > 0)
                                                .<small class="text-underline">{{number_format($productInfo->compare_price)}}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="pt-2 ps-sm-3 mx-auto mx-sm-0 text-center">
                                        <button class="btn btn-outline-danger btn-sm" type="button" onclick="removeProductWishlist('{{$productInfo->id}}')"><i class="fas fa-trash-alt me-2"></i>Remove</button>
                                    </div>
                                </div>  
                            @endforeach
                            @else
                            <div>
                                <h3>Wishlist no record</h3>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('customJs')
<script type="text/javascript">
    function removeProductWishlist($productId) {
        $.ajax({
            url: '{{ route("account.removeWishlist") }}',
            type:'POST',
            dataType: 'json',
            data:  {
                'productId' : $productId
                },
            success: function(response) {
                if(response.status == true) {
                    window.location.href = '{{ route("account.wishlist") }}';
                }
            },
            error: function() {

            }
        })
    }
</script>
@endsection