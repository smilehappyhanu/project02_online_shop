<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Email</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif;font-size:16px;">
    @if($mailData['userType'] == 'customer')
    <h1>Thanks for your order!!</h1>
    <h2>Your order id is: #{{$mailData['order']->id}}</h2>
    @else
    <h1>You have received an order.</h1>
    <h2>Order id is: #{{$mailData['order']->id}}</h2>
    @endif

    <h2>Shipping address</h2>
    <address>
        <strong>{{ $mailData['order']->first_name . ' '. $mailData['order']->last_name }}</strong><br>
        {{$mailData['order']->address}}<br>
        {{$mailData['order']->city}}, {{$mailData['order']->zip}}  {{ getCountryInfo($mailData['order']->country_id)->name}}<br>
        Phone: {{$mailData['order']->mobile}}<br>
        Email: {{$mailData['order']->email}}
    </address>
    <h2>Products:</h2>

    <div class="card-body table-responsive p-3">								
        <table class="table table-striped">
            <thead style="background-color: #ccc;">
                <tr>
                    <th>Product</th>
                    <th width="100">Price</th>
                    <th width="100">Qty</th>                                        
                    <th width="100">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mailData['order']->orderItems as $item)
                    <tr>
                        <td class="text-right">{{$item->name}}</td>
                        <td class="text-right">{{number_format($item->price)}}</td>                                        
                        <td class="text-right">{{$item->qty}}</td>
                        <td class="text-right">{{number_format($item->total)}}</td>
                    </tr>
                @endforeach
                <tr>
                    <th colspan="3" class="text-right">Subtotal:</th>
                    <td class="text-right">{{number_format($mailData['order']->subtotal)}}</td>
                </tr>
                
                <tr>
                    <th colspan="3" class="text-right">Shipping:</th>
                    <td class="text-right"> {{number_format($mailData['order']->shipping)}}</td>
                </tr>
                <tr>
                    <th colspan="3" class="text-right">Grand Total:</th>
                    <td class="text-right">{{number_format($mailData['order']->grand_total)}}</td>
                </tr>
            </tbody>
        </table>								
    </div>  
</body>
</html>