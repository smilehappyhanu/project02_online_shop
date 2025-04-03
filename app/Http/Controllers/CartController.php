<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use App\Models\Product;
use App\Models\Country;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CustomerAddress;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function addToCart(Request $request) {
        $product = Product::with('product_images')->find($request->id);
        $productImage = (!empty($product->product_images) ? $product->product_images->first() : '');
        if($product == null) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ]);
        }
        if (Cart::count() > 0) {
            // Product found in cart
            $cartContent = Cart::content();
            $productAlreadyExist = false;

            foreach($cartContent as $item) {
                if($item->id == $product->id) {
                    $productAlreadyExist = true;
                }
            }
            if ($productAlreadyExist == false) {
                Cart::add($product->id, $product->title, 1, $product->price,['productImage' => $productImage]);
                $status = true;
                $message = $product->title .' added in cart';

            } else {
                $status = false;
                $message = $product->title .' already added in cart';
            }

        } else {
            // Cart is empty
            // echo "Cart is empty now adding a product in cart";
            Cart::add($product->id, $product->title, 1, $product->price,['productImage' => $productImage]);
            $status = true;
            $message = $product->title .' added in cart';
        }
        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    public function cart () {
        $cartContent = Cart::content();
        return view('front.cart',compact('cartContent'));
    }

    public function updateCart (Request $request) {
        $rowId = $request->rowId;
        $qty = $request->qty;

        $itemInfo = Cart::get($rowId);
        $product = Product::find($itemInfo->id);
        // Check qty available in stock
        if($product->track_qty == 'Yes') {
            if($product->qty >= $qty) {
                Cart::update($rowId,$qty);
                $message = 'Cart updated successfully.';
                $status = true;
                session()->flash('success',$message);
            } else {
                $message = 'Requested qty ('.$qty.') is not available in stock';
                $status = false;
                session()->flash('error',$message);
            }
        } else {
            Cart::update($rowId,$qty);
            $message = 'Cart updated successfully.';
            $status = true;
            session()->flash('success',$message);
        }

        
        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }

    public function deleteItemCart (Request $request) {
        $rowId = $request->rowId;
        $itemInfo = Cart::get($rowId);
        if($itemInfo == null) {
            $err_msg = 'Item not found in cart.';
            session()->flash('error',$err_msg);
            return response()->json([
                'status' => false,
                'message' => $err_msg
            ]);
        }
        Cart::remove($request->rowId);
        $message = 'Item removed form cart successfully.';
        session()->flash('success',$message);
        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }

    public function checkout() {
        if(Cart::count() == 0) {
            return redirect()->route('front.cart');
        }
        if (Auth::check() == false) {
             if(!session()->has('url.intended')){
                 session(['url.intended' => url()->current()]);
             }
            return redirect()->route('account.login');
        } 
        $customerAddress = CustomerAddress::where('user_id',Auth::user()->id)->first();
        session()->forget('url.intended');
        $countries = Country::orderBy('name','ASC')->get();
        return view('front.checkout',compact('countries','customerAddress'));
    }

    public function handleCheckout (Request $request) {
        $validator = Validator::make($request->all(),[
            'first_name' => 'required|min:3',
            'last_name' => 'required',
            'email' => 'required|email',
            'country' => 'required',
            'address' => 'required|max:100',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'mobile' => 'required',
        ]);
        if($validator->passes()){
            $user = Auth::user(); // get user id
            // save to customer_address
            CustomerAddress::updateOrCreate(
                ['user_id' => $user->id ],
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
            // save orders table
            if($request->payment_method == 'cod') {
                $shipping = 0;
                $discount = 0;
                $subtotal = Cart::subtotal(2,'.','');
                $grandtotal = $subtotal+$shipping;

                $order = new Order;
                $order->subtotal = $subtotal;
                $order->shipping = $shipping;
                $order->grand_total = $grandtotal;
                $order->first_name = $request->first_name;
                $order->last_name = $request->last_name;
                $order->email = $request->email;
                $order->mobile = $request->mobile;
                $order->country_id = $request->country;
                $order->address = $request->address;
                $order->apartment = $request->apartment;
                $order->city = $request->city;
                $order->state = $request->state;
                $order->zip = $request->zip;
                $order->user_id = $user->id;
                $order->notes = $request->order_notes;
                $order->save();

                // save order_items table
                foreach (Cart::content() as $item) {
                    $orderItem = new OrderItem;
                    $orderItem->product_id = $item->id;
                    $orderItem->order_id = $order->id;
                    $orderItem->name = $item->name;
                    $orderItem->qty = $item->qty;
                    $orderItem->price = $item->price;
                    $orderItem->total = $item->qty*$item->price;
                    $orderItem->save();
                }
                Cart::destroy();
                session()->flash('success','You have ordered successfully.');
                return response()->json([
                    'status' => true,
                    'message' => 'Order saved successfully.',
                    'orderId' => $order->id
                ]);

            } else {

            }


        } else {
            return response()->json([
                'status' => false,
                'message' => 'Please fix the errors',
                'errors' => $validator->errors()
            ]);
        }
    }

    public function thankyou($id) {
        $id = $id;
        return view('front.thanks',compact('id'));
    }
}
