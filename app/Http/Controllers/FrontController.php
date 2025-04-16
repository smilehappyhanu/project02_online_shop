<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Page;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactEmail;

class FrontController extends Controller
{
    public function index () {
        $featuredProducts = Product::where('status',1)->where('is_featured','Yes')->orderBy('id','DESC')->get();
        $latestProducts = Product::orderBy('id','ASC')->where('status',1)->take(8)->get();
        return view('front.home',compact('featuredProducts','latestProducts'));
    }

    public function addToWishlist(Request $request) {
        if(Auth::check() == false) {
            // save URL of page opened previous
            session(['url.intended' => url()->previous()]);
            return response()->json([
                'status' => false,
            ]);
        }

        $product = Product::where('id',$request->id)->first();
        if($product == null) {
            return response()->json([
                'status' => true,
                'message' => '<div class="alert alert-danger">Product not found.</div>'
            ]);
        }
        Wishlist::updateOrCreate(
            [
                'user_id' => Auth::user()->id,
                'product_id' => $request->id
            ],
            [
                'user_id' => Auth::user()->id,
                'product_id' => $request->id
            ]
        );

        // $wishList = new Wishlist;
        // $wishList->user_id = Auth::user()->id;
        // $wishList->product_id = $request->id;
        // $wishList->save();

        return response()->json([
            'status' => true,
            'message' => '<div class="alert alert-success"><strong>"'.$product->title.'"</strong> added to your wishlist successfully.</div>'
        ]);
    }

    public function showStaticPage($lug) {
        $page = Page::where('slug',$lug)->first();
        if($page == null) {
            abort(404);
        }
        return view('front.page',compact('page'));
    }

    public function sendContactEmail (Request $request) {
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required|min:5',
            'message' => 'required'
        ]);
        if($validator->passes()) {
            //send email here for admin
            $mailData = [
                'name' => $request->name,
                'email' => $request->email,
                'subject' => $request->subject,
                'message' => $request->message,
                'mail_subject' => 'You have received a contact email.',
            ];
            $admin = User::where('id',1)->first();

            Mail::to($admin->email)->send(new ContactEmail($mailData));
            session()->flash('success','Thanks for contacting us, we will get back to you soon.');
            return response()->json([
                'status' => true,
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
}
