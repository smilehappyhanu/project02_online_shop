<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\SubCategory;

class ShopController extends Controller
{
    public function index (Request $request, $categorySlug = null, $subCategory = null) {
        $categorySelected = '';
        $subCategorySelected = '';
        $brandsArray = [];
    
        $categories = Category::orderBy('name','ASC')
                                ->with('sub_category')
                                ->where('status',1)
                                ->where('showHome','Yes')
                                ->take(8)
                                ->get();
        $brands = Brand::orderBy('name','ASC')->where('status',1)->get();

        // Apply filter here
        $products = Product::where('status',1);
        if(!empty($categorySlug)) {
            $category = Category::where('slug',$categorySlug)->first();
            $products = $products->where('category_id',$category->id);
            $categorySelected = $category->id;
        }
        if(!empty($subCategorySlug)) {
            $subCategory = SubCategory::where('slug',$subCategorySlug)->first();
            $products = $products->where('sub_category_id',$subCategory->id);
            $subCategorySelected = $subCategory->id;
        }
        if(!empty($request->get('brand'))){
            $brandsArray = explode(',',$request->get('brand'));
            $products = $products->whereIn('brand_id',$brandsArray);
        }

        if(!empty($request->get('search'))) {
            $products = $products->where('title','like','%'.$request->get("search").'%');
        }

        if($request->get('price_min') != '' && $request->get('price_max') != '') {
            if($request->get('price_max') == 1000 ) {
                $products = $products->whereBetween('price',[intval($request->get('price_min')),1000000]);
            } else {
                $products = $products->whereBetween('price',[intval($request->get('price_min')),intval($request->get('price_max'))]);
            }
        }
        $priceMin = intval($request->get('price_min'));
        $priceMax = (intval($request->get('price_max')) == 0 ? 1000 : $request->get('price_max'));
        $sort = $request->get('sort');
        if ($request->get('sort') != '') {
            if($request->get('sort') == 'latest') {
                $products = $products->orderBy('id','DESC');
            } else if ($request->get('sort') == 'price_asc') {
                $products = $products->orderBy('price','ASC');
            } else {
                $products = $products->orderBy('price','DESC');
            }
        } else {
            $products = $products->orderBy('id','DESC');
        }
       // $products = Product::orderBy('title','DESC')->where('status',1)->get();
       $products = $products->paginate(1);
        return view('front.shop',compact('categories','brands','products','categorySelected',
        'subCategorySelected','brandsArray','priceMin','priceMax','sort'));
    }

    public function product ($lug) {
        $product = Product::where('slug',$lug)->with('product_images')->first();
        if($product == null) {
            abort(404);
        }
        // Fetch related product
        $relatedProducts = [];
        if($product->related_products != '') {
            $productArray = explode(',',$product->related_products);
            $relatedProducts = Product::whereIn('id',$productArray)->with('product_images')->get();
        }

        return view('front.product',compact('product','relatedProducts'));
    }
}
