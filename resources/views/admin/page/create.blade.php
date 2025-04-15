@extends('admin.layouts.app')

@section('content')
			
<section class="content-header">					
    <div class="container-fluid my-2">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Create Page</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('pages.index') }}" class="btn btn-primary">Back</a>
            </div>
        </div>
    </div>
    <!-- /.container-fluid -->
</section>

<section class="content">
    <!-- Default box -->
    <div class="container-fluid">
        <form action="" method="POST" id="createPageForm" name="createPageForm">
            @csrf
            <div class="card">
                <div class="card-body">								
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name">Name</label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="Name">	
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="slug">Slug</label>
                                <input type="text" name="slug" id="slug" class="form-control" readonly placeholder="Slug">	
                                <p></p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="description">Description</label>
                                <textarea name="description" id="description" cols="30" rows="10" class="summernote" placeholder="Description"></textarea>
                            </div>
                        </div>			
                    </div>
                </div>							
            </div>
            <div class="pb-5 pt-3">
                <button class="btn btn-primary" type="submit">Create</button>
                <a href="{{ route('pages.index') }}" class="btn btn-outline-dark ml-3">Cancel</a>
            </div>
         </form>      
    </div>
    <!-- /.card -->
</section>
<!-- /.content -->

@endsection

@section('js')
<script>
    $('#name').change(function(){
        var element = $(this);
        $('button[type=submit]').prop('disabled',true);
        $.ajax({
            url: '{{ route("getSlug") }}',
            type: 'get',
            data: { title: element.val() },
            dataType: 'json',
            success: function (response) {
                $('button[type=submit]').prop('disabled',false);
                if(response['status'] == true) {
                    $('#slug').val(response['slug']);
                }
            },
            error: function (jqXHR, exception) {
                console.log('Something went wrong.');
            }
        }) 
    });


    $('#createPageForm').submit(function(event){
        event.preventDefault();
        var element = $(this);
        $("button[type=submit]").prop('disabled',true);
        $.ajax({
            url: '{{ route("pages.store") }}',
            type: 'post',
            data: element.serializeArray(),
            dataType: 'json',
            success: function (response) {
                $("button[type=submit]").prop('disabled',false);
                if (response['status'] == true) {
                    window.location.href="{{ route('pages.index') }}"
                    $('#name').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
                    $('#slug').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');

                } else {
                    var errors = response['errors'];
                    if(errors['name']) {
                        $('#name').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors['name']);
                    } else {
                        $('#name').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
                    }
                    if(errors['slug']) {
                        $('#slug').addClass('is-invalid').siblings('p').addClass('invalid-feedback').html(errors['slug']);
                    }else {
                        $('#slug').removeClass('is-invalid').siblings('p').removeClass('invalid-feedback').html('');
                    }
                }     
            },
            error: function (jqXHR, exception) {
                console.log('Something went wrong.');
            }
        }) 
    })

  
   
</script>

@endsection
		