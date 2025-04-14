<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login () {
        return view('front.account.login');
    }

    public function register () {
        return view('front.account.register');
    }

    public function handleRegister (Request $request) {
        $validator = Validator::make($request->all(),[
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:5'
        ]);
        if($validator->passes()) {
            $user = new User;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();

            $message = 'You have been registered successfully.';
            session()->flash('success',$message);
            return response()->json([
                'status' => true,
                'message' => $message
            ]);
        } else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function authenticate (Request $request) {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if($validator->passes()){
            if(Auth::attempt(['email' => $request->email,'password' => $request->password],$request->get('remember'))) {
                if(session()->has('url.intended')){
                    return redirect(session()->get('url.intended'));
                }
                return redirect()->route('account.profile');

            } else {
                // session()->flash('error','Either email/password is not correct.');
                return redirect()->route('account.login')->withInput($request->only('email'))
                                                        ->with('error','Either email/password is not correct.');
            }
        } else {
            return redirect()->route('account.login')
                            ->withErrors($validator)->withInput($request->only('email'));
        }
    }

    public function profile () {
        return view('front.account.profile');
    }

    public function logout () {
       Auth::logout();
       return redirect()->route('account.login')->with('success','You successfully logged out!');
    }

    public function orders () {
        $user = Auth::user();
        $orders = Order::where('user_id',$user->id)->orderBy('created_at','DESC')->get();
        return view('front.account.order',compact('orders'));
    }

    public function orderDetail ($id) {
        $user = Auth::user();
        $order = Order::where('user_id',$user->id)->where('id',$id)->first();
        $orderItems = OrderItem::where('order_id',$id)->get();
        $orderItemsCount = OrderItem::where('order_id',$id)->count();
        return view('front.account.orderDetail',compact('order','orderItems','orderItemsCount'));
    }

    public function wishlist() {
        $wishlists = Wishlist::where('user_id',Auth::user()->id)->get();
        return view('front.account.wishlist',compact('wishlists'));
    }

    public function removeProductWishlist(Request $request) {
        $wishList = Wishlist::where('user_id',Auth::user()->id)->where('product_id',$request->productId)->first();

        if($wishList == null) {
            session()->flash('error','Product already removed from wishlist.');
            return response()->json([
                'status' => true,
            ]);
        } else {
            Wishlist::where('user_id',Auth::user()->id)->where('product_id',$request->productId)->delete();
            session()->flash('success','Product removed from wishlist successfully.');
            return response()->json([
                'status' => true,
            ]);
        }
    }
}
