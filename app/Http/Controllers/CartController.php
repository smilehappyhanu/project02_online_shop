<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use App\Models\Product;

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
}
