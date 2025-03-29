<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class FrontController extends Controller
{
    public function index () {
        $featuredProducts = Product::where('status',1)->where('is_featured','Yes')->orderBy('id','DESC')->get();
        $latestProducts = Product::orderBy('id','ASC')->where('status',1)->take(8)->get();
        return view('front.home',compact('featuredProducts','latestProducts'));
    }
}
