<?php

use App\Models\Category;
use App\Models\ProductImage;
use App\Models\Product;
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

function getProductInfo($productId) {
    return Product::where('id',$productId)->first();
}

function orderEmail ($orderId, $userType="customer") {
    $order = Order::where('id',$orderId)->with('orderItems')->first();

    if($userType == 'customer') {
        $subject = 'Thanks for your order.';
        $emailTo = $order->email;

    } else {
        $subject = 'You have received an order.';
        $emailTo = "hoangtien88@gmail.com";
    }

    $mailData = [
        'subject' => $subject,
        'order'  => $order,
        'userType' => $userType
    ];

    Mail::to($emailTo)->send(new OrderEmail($mailData));
}

function getCountryInfo($id) {
    return Country::where('id',$id)->first();
}
?>

