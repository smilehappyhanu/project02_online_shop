<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\Shipping;
use Illuminate\Support\Facades\Validator;

class ShippingController extends Controller
{
    public function create () {
        $countries = Country::get();
        $shippingCharges = Shipping::select('shipping_charges.*','countries.name')
                                    ->leftJoin('countries','shipping_charges.country_id','countries.id')->get();
        return view('admin.shipping.create',compact('countries','shippingCharges'));
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(),[
            'country' => 'required',
            'amount' => 'required|numeric'
        ]);
        if($validator->passes()) {
            $count = Shipping::where('country_id',$request->country)->count();
            if($count > 0 ) {
                session()->flash('error','Shipping already added.');
                return response()->json([
                    'status' => true,
                ]);
            }
            $shipping = new Shipping;
            $shipping->country_id = $request->country;
            $shipping->amount = $request->amount;
            $shipping->save();

            session()->flash('success','Shipping added successfully.');
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

    public function edit ($id) {
        $shippingCharge = Shipping::find($id);
        $countries = Country::get();

        return view('admin.shipping.edit',compact('shippingCharge','countries'));
    }

    public function update ($id,Request $request) {
        $validator = Validator::make($request->all(),[
            'country' => 'required',
            'amount' => 'required|numeric'
        ]);
        if($validator->passes()) {
            $count = Shipping::where('country_id',$request->country)->count();
            if($count > 0 ) {
                session()->flash('error','Shipping already added.');
                return response()->json([
                    'status' => true,
                ]);
            }
            $shippingCharge = Shipping::find($id);
            $shippingCharge->country_id = $request->country;
            $shippingCharge->amount = $request->amount;
            $shippingCharge->save();

            session()->flash('success','Shipping updated successfully.');
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

    public function destroy ($id) {
        $shippingCharge = Shipping::find($id);
        if($shippingCharge == null) {
            session()->flash('error','Shipping not found');
            return response()->json([
                'status' => true,
            ]);
        }
        $shippingCharge->delete();
        session()->flash('success','Shipping deleted successfully.');
        return response()->json([
            'status' => true,
        ]);
    }
}
