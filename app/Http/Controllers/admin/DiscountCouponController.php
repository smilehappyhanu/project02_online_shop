<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use App\Models\DiscountCoupon;

class DiscountCouponController extends Controller
{
    public function index (Request $request) {
        $discountCoupons = DiscountCoupon::latest('id');

        if(!empty($request->get('keyword_search'))) {
            $discountCoupons = $discountCoupons->where('name','like','%'.$request->keyword_search.'%');
            $discountCoupons = $discountCoupons->orWhere('code','like','%'.$request->keyword_search.'%');
        }
        $discountCoupons = $discountCoupons->paginate(10);

        return view('admin.coupon.list',compact('discountCoupons'));

    }

    public function create () {
        return view ('admin.coupon.create');
    }

    public function store (Request $request) {

        $validator = Validator::make($request->all(),[
            'code' => 'required',
            'type' => 'required',
            'discount_amount' => 'required|numeric',
            'status' => 'required',
        ]);

        if($validator->passes()) {
            if(!empty($request->starts_at)) {
                $current_time = Carbon::now();
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->starts_at);

                if($startAt->lte($current_time) == true ) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['starts_at' => 'Start Date can not be less than current date time.'],
                    ]);
                }
            }

            if(!empty($request->starts_at)) {
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->starts_at);
                $expireAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->expires_at);

                if($expireAt->gt($startAt) == false ) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['expires_at' => 'Expire Date must be greater than start date time.'],
                    ]);
                }
            }

            $coupon = new DiscountCoupon;
            $coupon->code = $request->code;
            $coupon->name = $request->name;
            $coupon->description = $request->description;
            $coupon->max_uses = $request->max_uses;
            $coupon->max_uses_user = $request->max_uses_user;
            $coupon->type = $request->type;
            $coupon->discount_amount = $request->discount_amount;
            $coupon->min_amount = $request->min_amount;
            $coupon->status = $request->status;
            $coupon->starts_at = $request->starts_at;
            $coupon->expires_at = $request->expires_at;
            $coupon->save();

            $message = 'Discount coupon created successfully.';
            session()->flash('success',$message);
            return response()->json([
                'status' => true,
                'message' => $message
            ]);


        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit (Request $request,$id) {
        $coupon = DiscountCoupon::find($id);

        if($coupon == null) {
            session()->flash('error','Record not found');
            return redirect()->route('coupons.index');
        }
        return view('admin.coupon.edit',compact('coupon'));
    }

    public function update (Request $request, $id) {
        $coupon = DiscountCoupon::find($id);

        if ($coupon == null) {
            session()->flash('error','Record not found');
            return response()->json([
                'status' => true
            ]);
        }

        $validator = Validator::make($request->all(),[
            'code' => 'required',
            'type' => 'required',
            'discount_amount' => 'required|numeric',
            'status' => 'required',
        ]);

        if($validator->passes()) {

            if(!empty($request->starts_at)) {
                $startAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->starts_at);
                $expireAt = Carbon::createFromFormat('Y-m-d H:i:s',$request->expires_at);

                if($expireAt->gt($startAt) == false ) {
                    return response()->json([
                        'status' => false,
                        'errors' => ['expires_at' => 'Expire Date must be greater than start date time.'],
                    ]);
                }
            }

            $coupon->code = $request->code;
            $coupon->name = $request->name;
            $coupon->description = $request->description;
            $coupon->max_uses = $request->max_uses;
            $coupon->max_uses_user = $request->max_uses_user;
            $coupon->type = $request->type;
            $coupon->discount_amount = $request->discount_amount;
            $coupon->min_amount = $request->min_amount;
            $coupon->status = $request->status;
            $coupon->starts_at = $request->starts_at;
            $coupon->expires_at = $request->expires_at;
            $coupon->save();

            $message = 'Discount coupon updated successfully.';
            session()->flash('success',$message);
            return response()->json([
                'status' => true,
                'message' => $message
            ]);


        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy(Request $request,$id) {
        $coupon = DiscountCoupon::find($id);

        if($coupon == null) {
            session()->flash('error','Record not found');
            return redirect()->route('coupons.index');
        }

        $coupon->delete();
        session()->flash('success','Coupon deleted successfully.');
        return response()->json([
            'status' => true,
        ]);
    }
}
