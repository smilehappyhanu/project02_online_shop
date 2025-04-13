<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use App\Models\Product;
use App\Models\Country;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\CustomerAddress;
use App\Models\DiscountCoupon;
use App\Models\Shipping;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\Echo_;

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
        $discount = 0;
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
        $subtotal = Cart::subtotal(2,'.','');

        // Apply discount here
        if(session()->has('discount_code')) {
            $discount_code = session()->get('discount_code');
            if($discount_code->type == 'percent') {
                $discount = ($discount_code->discount_amount/100)*$subtotal;
            } else {
                $discount = $discount_code->discount_amount;
            }
        }

        // Calculate shipping fee here
        if($customerAddress != '') {
            $userCountry = $customerAddress->country_id;
            $shippingInfo = Shipping::where('country_id',$userCountry)->first();
            $totalQty = 0;
            $totalShippingCharge = 0;
            $grandtotal = 0;
            foreach(Cart::content() as $item){
                $totalQty += $item->qty;
            }  
            if(!empty($shippingInfo->amount)) {
                $totalShippingCharge += $totalQty*($shippingInfo->amount);
            } else {
                $totalShippingCharge = 0;
            }
            $grandtotal = ($subtotal - $discount) + $totalShippingCharge;
        } else {
            $grandtotal = $subtotal - $discount;
            $totalShippingCharge = 0;
        }
        
       
        return view('front.checkout',compact('countries','customerAddress','totalShippingCharge','grandtotal','discount'));
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
                $discountCodeId = '';
                $promoCode = '';
                $shipping = 0;
                $discount = 0;
                $subtotal = Cart::subtotal(2,'.','');
                $grandtotal = $subtotal+$shipping;

                // Apply discount here
                if(session()->has('discount_code')) {
                    $discount_code = session()->get('discount_code');
                    if($discount_code->type == 'percent') {
                        $discount = ($discount_code->discount_amount/100)*$subtotal;
                    } else {
                        $discount = $discount_code->discount_amount;
                    }
                    $discountCodeId = $discount_code->id;
                    $promoCode = $discount_code->code;
                }

                $shippingInfo = Shipping::where('country_id',$request->country)->first();
                $totalQty = 0;
                foreach(Cart::content() as $item) {
                    $totalQty += $item->qty;
                }

                if($shippingInfo != null) {
                    $shipping = $totalQty*$shippingInfo->amount;
                    $grandtotal = $shipping + $subtotal - $discount;
                    
                } else {
                    $shippingInfo = Shipping::where('country_id','rest_of_world')->first();
                    $shipping = $totalQty*$shippingInfo->amount;
                    $grandtotal = $shipping + $subtotal - $discount;
                
                }


                $order = new Order;
                $order->subtotal = $subtotal;
                $order->shipping = $shipping;
                $order->grand_total = $grandtotal;
                $order->discount = $discount;
                $order->coupon_code_id = $discountCodeId;
                $order->coupon_code = $promoCode;
                $order->payment_status = 'not paid';
                $order->status = 'pending';
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

                // Send mail order for customer
                orderEmail($order->id);

                Cart::destroy();
                session()->forget('discount_code');
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

    public function getOrderSummery(Request $request) {
        $subtotal = Cart::subtotal(2,'.','');
        $discount = 0;
        $discountString = '';

        // Apply discount here
        if(session()->has('discount_code')) {
            $discount_code = session()->get('discount_code');
            if($discount_code->type == 'percent') {
                $discount = ($discount_code->discount_amount/100)*$subtotal;
            } else {
                $discount = $discount_code->discount_amount;
            }
            $discountString = '
                <div class="mt-4" id="discount-response">
                    <strong>'.session()->get('discount_code')->code.'</strong>
                    <a href="" class="btn btn-danger btn-sm" id="remove_discount"><i class="fa fa-times"></i></a>
                </div> ';
        }

        if($request->country_id >0) {
            $shippingInfo = Shipping::where('country_id',$request->country_id)->first();
            $totalQty = 0;
            foreach(Cart::content() as $item) {
                $totalQty += $item->qty;
            }
            if($shippingInfo != null) {
                $shippingCharge = $totalQty*$shippingInfo->amount;
                $grandtotal = $shippingCharge + $subtotal - $discount;
                return response()->json([
                    'status' => true,
                    'shippingCharge' => number_format($shippingCharge),
                    'discount' => $discount,
                    'discountString' => $discountString,
                    'grandtotal' => number_format($grandtotal)
                ]);
            } else {
                $shippingInfo = Shipping::where('country_id','rest_of_world')->first();
                $shippingCharge = $totalQty*$shippingInfo->amount;
                $grandtotal = $shippingCharge + $subtotal - $discount;
                return response()->json([
                    'status' => true,
                    'shippingCharge' => number_format($shippingCharge),
                    'discount' => $discount,
                    'discountString' => $discountString,
                    'grandtotal' => number_format($grandtotal)
                ]);
            }

        } else {
            return response()->json([
                'status' => true,
                'shippingCharge' => 0,
                'discount' => $discount,
                'discountString' => $discountString,
                'grandtotal' => number_format($subtotal - $discount) 
            ]);
        }
    }

    public function applyDiscount (Request $request) {
        $discount_code = DiscountCoupon::where('code',$request->discount_code)->first();
       
        // Check coupon code input is exist on DB or not
        if($discount_code == null) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid discount coupon.'
            ]);
        }
        $now = Carbon::now();
    
        // Check coupon start date is valid or not
        if($discount_code->starts_at != "") {
            $startDate = Carbon::createFromFormat('Y-m-d H:i:s',$discount_code->starts_at);
            if($now->lt($startDate)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Discount coupon is not using because start date is futures.'
                ]);
            }
        }
        // Check coupon expire or not 
        if($discount_code->starts_at != "") {
            $endDate = Carbon::createFromFormat('Y-m-d H:i:s',$discount_code->expires_at);
            if($now->gt($endDate)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Discount coupon is not using because end date is past.'
                ]);
            }
        }
        
        // Check coupon used how many times

        if($discount_code->max_uses > 0) {
            $couponUsed = Order::where('coupon_code_id',$discount_code->id)->count();
            if($couponUsed >= $discount_code->max_uses) {
                return response()->json([
                    'status' => false,
                    'message' => 'You can not use this coupon because max_uses went to maximum.'
                ]);
            }
        }
        

        // Check coupon used by a uses is max or not
        if($discount_code->max_uses_user > 0) {
            $couponUsedByOneUser = Order::where(['coupon_code_id' => $discount_code->id,'user_id' => Auth::user()->id])->count();
            if($couponUsedByOneUser >= $discount_code->max_uses_user) {
                return response()->json([
                    'status' => false,
                    'message' => 'You can not use this coupon because max_uses_user went to maximum.'
                ]);
            }
        }
        
        // Check min amount
        $subtotal = Cart::subtotal(2,'.','');
        if($discount_code->min_amount >0) {
            if($subtotal < $discount_code->min_amount) {
                return response()->json([
                    'status' => false,
                    'message' => 'If you want to this coupon, Your min amount must be'.$discount_code->min_amount.'.'
                ]);
            }
        }

        session()->put('discount_code',$discount_code);
        return $this->getOrderSummery($request);
        
    }

    public function removeDiscount(Request $request) {
        session()->forget('discount_code');
        return $this->getOrderSummery($request);
    }
}
