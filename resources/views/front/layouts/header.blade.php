<div class="bg-light top-header">        
	<div class="container">
		<div class="row align-items-center py-3 d-none d-lg-flex justify-content-between">
			<div class="col-lg-4 logo">
				<a href="{{route('front.home')}}" class="text-decoration-none">
					<span class="h1 text-uppercase text-primary bg-dark px-2">Tien's</span>
					<span class="h1 text-uppercase text-dark bg-primary px-2 ml-n1">Store</span>
				</a>
			</div>
			<div class="col-lg-6 col-6 text-left  d-flex justify-content-end align-items-center">
				@if(Auth::check())
				<a href="{{route('account.profile')}}" class="nav-link text-dark">My Account</a>
				@else
				<a href="{{route('account.login')}}" class="nav-link text-dark">Login/Register</a>
				@endif
				<form action="{{ route('front.shop') }}" method="GET">					
					<div class="input-group">
						<input type="text" placeholder="Search For Products" class="form-control" name="search" id="search" value="{{ Request::get('search') }}">
						<button class="input-group-text" type="submit">
							<i class="fa fa-search"></i>
						</button>
					</div>
				</form>
			</div>		
		</div>
	</div>
</div>

<header class="bg-dark">
	<div class="container">
		<nav class="navbar navbar-expand-xl" id="navbar">
			<a href="{{route('front.home')}}" class="text-decoration-none mobile-logo">
				<span class="h2 text-uppercase text-primary bg-dark">Tien's</span>
				<span class="h2 text-uppercase text-white px-2">Store</span>
			</a>
			<button class="navbar-toggler menu-btn" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      			<!-- <span class="navbar-toggler-icon icon-menu"></span> -->
				  <i class="navbar-toggler-icon fas fa-bars"></i>
    		</button>
    		<div class="collapse navbar-collapse" id="navbarSupportedContent">
      			<ul class="navbar-nav me-auto mb-2 mb-lg-0">
        			<!-- <li class="nav-item">
          				<a class="nav-link active" aria-current="page" href="index.php" title="Products">Home</a>
        			</li> -->
                    @if(getCategories()->isNotEmpty())
                    @foreach(getCategories() as $category)
					<li class="nav-item dropdown">
						<button class="btn btn-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
							{{$category->name}}
						</button>
                        @if($category->sub_category->isNotEmpty())
                        <ul class="dropdown-menu dropdown-menu-dark">
                        @foreach($category->sub_category as $subCategory)						
							<li><a class="dropdown-item nav-link" href="{{ route('front.shop',[$category->slug,$subCategory->slug]) }}">{{$subCategory->name}}</a></li>
						@endforeach
						</ul>
                        @endif
					</li>
                    @endforeach
                    @endif
					
      			</ul>      			
      		</div>   
			<div class="right-nav py-0">
				<a href="{{route('front.cart')}}" class="ml-3 d-flex pt-2">
					<i class="fas fa-shopping-cart text-primary"></i>					
				</a>
			</div> 		
      	</nav>
  	</div>
</header>