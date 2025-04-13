<?php

use App\Models\Category;
use App\Models\ProductImage;
use App\Models\Order;
use App\Models\Country;
use App\Mail\OrderEmail;
use Illuminate\Support\Facades\Mail;

function getCategories () {
    return Category::orderBy('name','ASC')
                     ->with('sub_category')
                     ->where('showHome','Yes')
                     ->where('status',1)
                     ->orderBy('id','DESC')
                     ->get();
}

function getProductImage($productId) {
    return ProductImage::where('product_id',$productId)->first();
}

function orderEmail ($orderId) {
    $order = Order::where('id',$orderId)->with('orderItems')->first();
    $mailData = [
        'subject' => 'Thanks for your order.',
        'order'  => $order
    ];

    Mail::to($order->email)->send(new OrderEmail($mailData));
}

function getCountryInfo($id) {
    return Country::where('id',$id)->first();
}
?>

