@extends('front.layouts.app')
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
            <form action="" method="post" name="orderForm" id="orderForm">
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
                                            <input type="text" name="first_name" id="first_name" class="form-control" placeholder="First Name" 
                                            value="{{ (!empty($customerAddress)) ? $customerAddress->first_name : '' }}">
                                            <p></p>
                                        </div>            
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Last Name"
                                            value="{{ (!empty($customerAddress)) ? $customerAddress->last_name : '' }}">
                                            <p></p>
                                        </div>            
                                    </div>
                                    
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <input type="text" name="email" id="email" class="form-control" placeholder="Email" value="{{ (!empty($customerAddress)) ? $customerAddress->email : '' }}">
                                            <p></p>
                                        </div>            
                                    </div>

                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <select name="country" id="country" class="form-control">
                                                <option value="">Select a Country</option>
                                                @if($countries->isNotEmpty())
                                                @foreach($countries as $country)
                                                    <option {{ (!empty($customerAddress) && $customerAddress->country_id == $country->id) ? 'selected' : '' }}
                                                    value="{{$country->id}}">{{$country->name}}</option>                                              
                                                @endforeach
                                                @endif
                                            </select>
                                            <p></p>
                                        </div>            
                                    </div>

                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <textarea name="address" id="address" cols="30" rows="3" placeholder="Address" class="form-control">
                                            {{ (!empty($customerAddress)) ? $customerAddress->address : '' }}
                                            </textarea>
                                            <p></p>
                                        </div>            
                                    </div>

                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <input type="text" name="apartment" id="apartment" class="form-control" placeholder="Apartment, suite, unit, etc. (optional)"
                                            value="{{ (!empty($customerAddress)) ? $customerAddress->apartment : '' }}">
                                        </div>            
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <input type="text" name="city" id="city" class="form-control" placeholder="City" value="{{ (!empty($customerAddress)) ? $customerAddress->city : '' }}">
                                            <p></p>
                                        </div>            
                                    </div>

                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <input type="text" name="state" id="state" class="form-control" placeholder="State" value="{{ (!empty($customerAddress)) ? $customerAddress->state : '' }}">
                                            <p></p>
                                        </div>            
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <input type="text" name="zip" id="zip" class="form-control" placeholder="Zip" value="{{ (!empty($customerAddress)) ? $customerAddress->zip : '' }}">
                                            <p></p>
                                        </div>            
                                    </div>

                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <input type="text" name="mobile" id="mobile" class="form-control" placeholder="Mobile No." value="{{ (!empty($customerAddress)) ? $customerAddress->mobile : '' }}">
                                            <p></p>
                                        </div>            
                                    </div>
                                    

                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <textarea name="order_notes" id="order_notes" cols="30" rows="2" placeholder="Order Notes (optional)" class="form-control"></textarea>
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
                                @foreach(Cart::content() as $item)
                                <div class="d-flex justify-content-between pb-2">
                                    <div class="h6">{{$item->name}} x {{$item->qty}}</div>
                                    <div class="h6">{{number_format($item->price*$item->qty)}}</div>
                                </div>
                            @endforeach
                                <div class="d-flex justify-content-between summery-end">
                                    <div class="h6"><strong>Subtotal</strong></div>
                                    <div class="h6"><strong id="discount_value">{{Cart::subtotal()}}</strong></div>
                                </div>
                                <div class="d-flex justify-content-between summery-end">
                                    <div class="h6"><strong>Discount</strong></div>
                                    <div class="h6"><strong>{{ $discount }}</strong></div>
                                </div>
                                <div class="d-flex justify-content-between mt-2">
                                    <div class="h6"><strong>Shipping</strong></div>
                                    <div class="h6"><strong id="shippingAmount">{{ ($totalShippingCharge != null) ? (number_format($totalShippingCharge,2)) : 0 }}</strong></div>
                                </div>
                                <div class="d-flex justify-content-between mt-2 summery-end">
                                    <div class="h5"><strong>Total</strong></div>
                                    <div class="h5"><strong id="grandTotal">{{($grandtotal != null) ? (number_format($grandtotal,2)) : 0}}</strong></div>
                                </div>                            
                            </div>
                        </div>   
                        
                        <div class="input-group apply-coupan mt-4">
                            <input type="text" placeholder="Coupon Code" class="form-control" name="discount_code" id="discount_code">
                            <button class="btn btn-dark" type="button" id="apply_discount">Apply Coupon</button>
                        </div> 


                        <div id="discount-response-wrapper">
                        @if(Session::has('discount_code'))
                            <div class="mt-4" id="discount-response">
                                <strong>{{Session::get('discount_code')->code }}</strong>
                                <a href="" class="btn btn-danger btn-sm" id="remove_discount"><i class="fa fa-times"></i></a>
                            </div> 
                        @endif
                        </div>
                        
                        <div class="card payment-form "> 
                            <h3 class="card-title h5 mb-3">Payment Details</h3>

                            <div class="form-check">
                                <input type="radio" name="payment_method" value="cod" id="payment_method_one" checked>
                                <label for="payment_method_one" class="form-check-label">COD</label>
                            </div>    
                            <div class="form-check">
                                <input type="radio" name="payment_method" value="stripe" id="payment_method_two">
                                <label for="payment_method_two" class="form-check-label">STRIPE</label>
                            </div>    

                            <div class="card-body p-0 d-none mt-3" id="card-payment-form">
                                <div class="mb-3">
                                    <label for="card_number" class="mb-2">Card Number</label>
                                    <input type="text" name="card_number" id="card_number" placeholder="Valid Card Number" class="form-control">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="expiry_date" class="mb-2">Expiry Date</label>
                                        <input type="text" name="expiry_date" id="expiry_date" placeholder="MM/YYYY" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="expiry_date" class="mb-2">CVV Code</label>
                                        <input type="text" name="expiry_date" id="expiry_date" placeholder="123" class="form-control">
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

@section('customJs')
<script type="text/javascript">
    $("#payment_method_one").click(function(){
        if($(this).is(":checked") == true) {
            $("#card-payment-form").addClass('d-none');
        }
    });

    $("#payment_method_two").click(function(){
        if($(this).is(":checked") == true) {
            $("#card-payment-form").removeClass('d-none');
        }
    });

    $("#orderForm").submit(function(event){
        event.preventDefault();
        $("button[type='submit']").prop('disabled',true);
        $.ajax({
            url: '{{ route("front.handleCheckout") }}',
            type:'post',
            data: $(this).serializeArray(),
            dataType: 'json',
            success: function(response) {
                $("button[type='submit']").prop('disabled',false);
                var errors = response.errors;
                if(response.status == false) {
                    if(errors.first_name) {
                        $("#first_name").addClass('is-invalid').siblings("p").addClass('invalid-feedback').html(errors.first_name);
                    } else {
                        $("#first_name").removeClass('is-invalid').siblings("p").removeClass('invalid-feedback').html('');
                    }

                    if(errors.last_name) {
                        $("#last_name").addClass('is-invalid').siblings("p").addClass('invalid-feedback').html(errors.last_name);
                    } else {
                        $("#last_name").removeClass('is-invalid').siblings("p").removeClass('invalid-feedback').html('');
                    }

                    if(errors.email) {
                        $("#email").addClass('is-invalid').siblings("p").addClass('invalid-feedback').html(errors.email);
                    } else {
                        $("#email").removeClass('is-invalid').siblings("p").removeClass('invalid-feedback').html('');
                    }

                    if(errors.country) {
                        $("#country").addClass('is-invalid').siblings("p").addClass('invalid-feedback').html(errors.country);
                    } else {
                        $("#country").removeClass('is-invalid').siblings("p").removeClass('invalid-feedback').html('');
                    }

                    if(errors.address) {
                        $("#address").addClass('is-invalid').siblings("p").addClass('invalid-feedback').html(errors.address);
                    } else {
                        $("#address").removeClass('is-invalid').siblings("p").removeClass('invalid-feedback').html('');
                    }

                    if(errors.city) {
                        $("#city").addClass('is-invalid').siblings("p").addClass('invalid-feedback').html(errors.city);
                    } else {
                        $("#city").removeClass('is-invalid').siblings("p").removeClass('invalid-feedback').html('');
                    }

                    if(errors.state) {
                        $("#state").addClass('is-invalid').siblings("p").addClass('invalid-feedback').html(errors.state);
                    } else {
                        $("#state").removeClass('is-invalid').siblings("p").removeClass('invalid-feedback').html('');
                    }

                    if(errors.zip) {
                        $("#zip").addClass('is-invalid').siblings("p").addClass('invalid-feedback').html(errors.zip);
                    } else {
                        $("#zip").removeClass('is-invalid').siblings("p").removeClass('invalid-feedback').html('');
                    }

                    if(errors.mobile) {
                        $("#mobile").addClass('is-invalid').siblings("p").addClass('invalid-feedback').html(errors.mobile);
                    } else {
                        $("#mobile").removeClass('is-invalid').siblings("p").removeClass('invalid-feedback').html('');
                    }
                } else {
                    window.location.href = "{{ url('/thanks/') }}/" + response.orderId;
                }
                
            },
            error: function() {

            }
        })
    });

    $("#country").change(function(){
        $.ajax({
            url: '{{ route("front.getOrderSummery") }}',
            type: 'post',
            data: {
                'country_id' : $(this).val()
            },
            dataType: 'json',
            success: function (response) {
                if(response.status == true) {
                    $("#shippingAmount").html(response.shippingCharge);
                    $("#grandTotal").html(response.grandtotal);
                }
            },
            error: function () {

            }
        })
    });

    $("#apply_discount").click(function(){
        $.ajax({
            url: '{{ route("front.applyDiscount") }}',
            type: 'post',
            data: {
                'discount_code' : $("#discount_code").val(),
                'country' : $("#country").val()
            },
            dataType: 'json',
            success: function (response) {
                if(response.status == true) {
                    $("#shippingAmount").html(response.shippingCharge);
                    $("#grandTotal").html(response.grandtotal);
                    $("#discount_value").html(response.discount);
                   // $("#discount-response-wrapper").html(response.discountString);

                } else {
                    $("#discount-response-wrapper").html("<span class='text-danger'>"+response.message+"</span>");

                }
            },
            error: function () {

            }
        })
    });

    // $('body').on('click',"remove_discount",function(){
        
    // })

    $('#remove_discount').click(function(){
        $.ajax({
            url: '{{ route("front.removeDiscount") }}',
            type: 'post',
            data: {
                'country' : $("#country").val()
            },
            dataType: 'json',
            success: function (response) {
                if(response.status == true) {
                    $("#shippingAmount").html(response.shippingCharge);
                    $("#grandTotal").html(response.grandtotal);
                    $("#discount_value").html(response.discount);
                    $("#discount-response").html('');
                    $('#discount_code').val('');
                }
            },
            error: function () {

            }
        })
    })
    
</script>
@endsection