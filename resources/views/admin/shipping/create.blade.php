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
        <form action="" method="POST" id="shippingForm" name="shippingForm">
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
                                        <option value="{{ $country->id }}">{{ $country->name }}</option>
                                    @endforeach
                                        <option value="rest_of_world">Rest of the world</option>
                                    @endif
                                </select>
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="amount">Amount</label>
                            <input type="text" class="form-control" name="amount" id="amount" placeholder="Amount">
                            <p></p>
                        </div>           								
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-primary" type="submit">Create</button>
                        <a href="" class="btn btn-outline-dark ml-3">Cancel</a>
                    </div>	
                </div>					
            </div>        
         </form> 

         <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-striped">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>Action</th>
                            </tr>
                            @if($shippingCharges->isNotEmpty())
                            @foreach($shippingCharges as $shippingCharge)
                            <tr>
                                <td>{{ $shippingCharge->id }}</td>
                                <td>{{ ($shippingCharge->country_id) == 'rest_of_world' ? 'Rest of the world' : $shippingCharge->name }}</td>
                                <td>{{ $shippingCharge->amount }}</td>
                                <td>
                                    <a href="{{ route('shipping.edit',$shippingCharge->id) }}" class="btn btn-primary">Edit</a>
                                    <a href="javascript:void(0);" class="btn btn-danger" onclick="deleteRecord('{{$shippingCharge->id}}')">Delete</a>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                        </table>
                    </div>
                </div>
            </div>
         </div>     
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
        url: '{{ route("shipping.store") }}',
        type:'post',
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

function deleteRecord(id) {
    var url = "{{route('shipping.delete','ID')}}";
    var newUrl = url.replace("ID",id);
    if(confirm('Are you sure to delete this record')) {
        $.ajax({
        url: newUrl,
        type: 'delete',
        data: {},
        headers: {
        'X-CSRF-TOKEN' : $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        success: function (response) {
            if(response["status"] == true) {
                window.location.href = "{{route('shipping.create') }}";
            }
        },
    })
    }  
}
</script>

@endsection
		