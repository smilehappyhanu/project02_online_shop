<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index (Request $request) {
        $orders = Order::latest('orders.created_at')->select('orders.*','users.name','users.email');
        $orders = $orders->leftJoin('users','users.id','orders.user_id');


        if($request->get('keyword_search') != '') {
            $orders = $orders->where('users.name','like','%'.$request->keyword_search.'%');
            $orders = $orders->orWhere('users.email','like','%'.$request->keyword_search.'%');
            $orders = $orders->orWhere('orders.id','like','%'.$request->keyword_search.'%');
        }

        $orders = $orders->paginate(10);
        return view('admin.order.list',compact('orders'));
    }

    public function detail ($orderId) {
        $order = Order::select('orders.*','countries.name as CountryName','countries.code')
                    ->where('orders.id',$orderId)
                    ->leftJoin('countries','countries.id','orders.country_id')
                    ->first();
        $orderItems = OrderItem::select('order_items.*','products.title as ProductName')
                    ->where('order_id',$orderId)
                    ->leftJoin('products','products.id','order_items.product_id')
                    ->get();

        return view('admin.order.detail',compact('order','orderItems'));
    }

    public function changeOrderStatus (Request $request, $orderId) {
        $order = Order::find($orderId);
        $order->status = $request->status;
        $order->shipped_date = $request->shipped_date;
        $order->save();

        session()->flash('success','Order status changed successfully.');
        return response()->json([
            'status' => true,
            'message' => 'Order status changed successfully.'
        ]);
    }

    public function sendInvoiceEmail (Request $request,$orderId) {
        orderEmail($orderId,$request->userType);

        $message = 'Order email sent successfully.';
        
        session()->flash('success',$message);
        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }
}
