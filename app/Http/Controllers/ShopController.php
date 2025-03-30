<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\SubCategory;

class ShopController extends Controller
{
    public function index () {
        $categories = Category::orderBy('name','ASC')
                                ->with('sub_category')
                                ->where('status',1)
                                ->where('showHome','Yes')
                                ->take(8)
                                ->get();
        $brands = Brand::orderBy('name','ASC')->where('status',1)->get();
        $products = Product::orderBy('title','DESC')->where('status',1)->get();
       
        return view('front.shop',compact('categories','brands','products'));
    }
}
