<?php

namespace App\Http\Controllers;

use App\Mail\ResetPassword;
use App\Models\Country;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Wishlist;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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
        $userId = Auth::user()->id;
        $user = User::where('id',$userId)->first();
        $countries = Country::orderBy('name','ASC')->get();
        $customerAddress = CustomerAddress::where('user_id',$userId)->first();
    
        return view('front.account.profile',compact('user','countries','customerAddress'));
    }

    public function updateProfile (Request $request) {
        $userId = Auth::user()->id;
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$userId.',id',
            'phone' => 'required'
        ]);
        if($validator->passes()) {
            $user = User::find($userId);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->save();

            $message = 'User info updated successfully.';
            session()->flash('success',$message);
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

    public function updateAddress(Request $request) {
        $userId = Auth::user()->id;
        $validator = Validator::make($request->all(),[
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'address' => 'required|max:30',
            'apartment' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'mobile' => 'required',
        ]);
        if($validator->passes()) {
            CustomerAddress::updateOrCreate(
                ['user_id' => $userId],
                [
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'mobile' => $request->mobile,
                    'country_id' => $request->country,
                    'address' => $request->address,
                    'apartment' => $request->apartment,
                    'city' => $request->city,
                    'state' => $request->state,
                    'zip' => $request->zip,
                ]
            );

            $message = 'Address info updated successfully.';
            session()->flash('success',$message);
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

    public function changePassword () {
        return view('front.account.change-pw');
    }

    public function handleChangePw (Request $request) {
        $validator = Validator::make($request->all(),[
            'old_password' => 'required',
            'new_password' => 'required|min:8|same:confirm_password',
            'confirm_password' => 'required'
        ]);
        if($validator->passes()) {
            $user = User::select('id','password')->where('id', Auth::user()->id)->first();
            if(!Hash::check($request->old_password,$user->password)) {
                session()->flash('error','Your old password is not correct, please try again.');
                return response()->json([
                    'status' => true,
                ]);
            }
            User::where('id',$user->id)->update([
                'password' => Hash::make($request->new_password)
            ]);
            session()->flash('success','You have updated password successfully.');
            return response()->json([
                'status' => true
            ]);

        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function forgotPassword () {
        return view('front.account.forgot-pw');
    }
    
    public function handleSendMailForgotPw(Request $request) {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email|exists:users,email'
        ]);
        if($validator->passes()) {
            $token = Str::random(60);
            DB::table('password_resets')->where('email',$request->email)->delete();

            DB::table('password_resets')->insert([
                'email' => $request->email,
                'token' => $token,
                'created_at' => now()
            ]);

            // Send email here
            $user = User::where('email',$request->email)->first();
            $formData = [
                'token' => $token,
                'user' => $user,
                'mailSubject' => 'You have requested to reset password.'
            ];

            Mail::to($request->email)->send(new ResetPassword($formData));

            session()->flash('success','Please check your mailbox to complete reset password.');

            return redirect()->route('front.forgotPassword');

        } else {
            return redirect()->route('front.forgotPassword')->withInput()->withErrors($validator);
        }
    }

    public function showFormResetPw($token) {
        $tokenExist = DB::table('password_resets')->where('token',$token)->first();

        if($tokenExist == null) {
            return redirect()->route('front.forgotPassword')->with('error','Invalid request');
        }

        return view('front.account.reset-pw',compact('token'));
    }

    public function handleResetPw(Request $request) {
        $token = $request->token;
        $tokenObj = DB::table('password_resets')->where('token',$token)->first();

        if($tokenObj == null) {
            return redirect()->route('front.forgotPassword')->with('error','Invalid request');
        }
        $user = User::where('email',$tokenObj->email)->first();
        $validator = Validator::make($request->all(),[
            'new_password' => 'required|min:8|max:24',
            'confirm_password' => 'required|min:8|max:24|same:new_password',
        ]);
        if($validator->passes()) {
            User::where('id',$user->id)->update([
                'password' => Hash::make($request->new_password)
            ]);
            DB::table('password_resets')->where('email',$user->email)->delete();

            return redirect()->route('account.login')->with('success','You have reset password successfully.');
            
        } else {
            return redirect()->route('front.showFormResetPw',$token)->withInput()->withErrors($validator);
        }
    }


}

