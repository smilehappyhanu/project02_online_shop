@extends('front.layouts.app')
@section('content')

    <section class="section-5 pt-3 pb-3 mb-3 bg-white">
        <div class="container">
        @if(Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{Session::get('success')}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        @if(Session::has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{Session::get('error')}}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
            <div class="light-font">
                <ol class="breadcrumb primary-color mb-0">
                    <li class="breadcrumb-item"><a class="white-text" href="{{ route('front.home') }}">Home</a></li>
                    <li class="breadcrumb-item">Reset password</li>
                </ol>
            </div>
        </div>
    </section>

    <section class=" section-10">
        <div class="container">
            <div class="login-form">    
                <form action="{{ route('front.handleResetPw') }}" method="post" name="handleResetPw" id="handleResetPw">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <h4 class="modal-title">Reset password</h4>
                    <div class="form-group">
                        <input type="text" class="form-control @error('new_password') is-invalid @enderror" placeholder="New Password" name="new_password"  id="new_password"> 
                        @error('new_password')
                            <p class="invalid-feedback">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control @error('confirm_password') is-invalid @enderror" placeholder="Confirm Password" name="confirm_password"  id="confirm_password"> 
                        @error('confirm_password')
                            <p class="invalid-feedback">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <input type="submit" class="btn btn-dark btn-block btn-lg" value="Reset">              
                </form>			
                <div class="text-center small">You have an account? <a href="{{route('account.login')}}">Login</a></div>
            </div>
        </div>
    </section>
@endsection