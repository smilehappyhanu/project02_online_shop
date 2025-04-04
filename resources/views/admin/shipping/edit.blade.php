@extends('admin.layouts.app')

@section('content')
			
<section class="content-header">					
    <div class="container-fluid my-2">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Shipping Management</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('categories.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
</section>

<section class="content">
    <!-- Default box -->
    <div class="container-fluid">
        @include('admin.message')
        <form action="" method="PUT" id="shippingForm" name="shippingForm">
            @csrf
            <div class="card">
                <div class="card-body">								
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="country">Country</label>
                                <select name="country" id="country" class="form-control">
                                    <option value="">Select a country</option>
                                    @if($countries->isNotEmpty())
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ $shippingCharge->country_id == $country->id ? 'selected' : '' }}>{{ $country->name }}</option>
                                    @endforeach
                                        <option value="rest_of_world" {{ $shippingCharge->country_id == 'rest_of_world' ? 'selected' : '' }}>Rest of the world</option>
                                    @endif
                                </select>
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="amount">Amount</label>
                            <input type="text" class="form-control" name="amount" id="amount" placeholder="Amount" value="{{ $shippingCharge->amount }}">
                            <p></p>
                        </div>           								
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-primary" type="submit">Update</button>
                        <a href="" class="btn btn-outline-dark ml-3">Cancel</a>
                    </div>	
                </div>					
            </div>        
         </form> 

    </div>
    <!-- /.card -->
</section>
<!-- /.content -->

@endsection

@section('js')
<script>

$("#shippingForm").submit(function(event){
    event.preventDefault();
    $("button[type='submit']").prop('disabled',true);
    $.ajax({
        url: '{{ route("shipping.update",$shippingCharge->id) }}',
        type:'put',
        data: $(this).serializeArray(),
        dataType: 'json',
        success: function(response) {
            $("button[type='submit']").prop('disabled',false);
            if (response.status == false) {
                var errors = response.errors;
                if(errors.country) {
                    $("#country").addClass('is-invalid').siblings("p").addClass('invalid-feedback').html(errors.country);
                } else {
                    $("#country").removeClass('is-invalid').siblings("p").removeClass('invalid-feedback').html('');
                }

                if(errors.amount) {
                    $("#amount").addClass('is-invalid').siblings("p").addClass('invalid-feedback').html(errors.amount);
                } else {
                    $("#amount").removeClass('is-invalid').siblings("p").removeClass('invalid-feedback').html('');
                }
            } else {
                 window.location.href = '{{ route("shipping.create") }}';
            }
        },
        error: function () {

        }
    })
})
</script>

@endsection
		