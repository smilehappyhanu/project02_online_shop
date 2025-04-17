<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;


class HomeController extends Controller
{
    public function index () {
       $totalOrders = Order::where('status','!=', 'canceled')->count();
       $totalProducts = Product::count();
       $totalCustomers = User::where('role',1)->count();
       $totalRevenue = Order::where('status','!=', 'canceled')->sum('grand_total');

       // This month revenue
       $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
       $currentDate = Carbon::now()->format('Y-m-d');

       $revenueThisMonth = Order::where('status','!=', 'canceled')
                        ->whereDate('created_at','>=',$startOfMonth)
                        ->whereDate('created_at','<=',$currentDate)
                        ->sum('grand_total');

        // Last month revenue
        $lastMonthStartDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
        $lastMonthEndDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        $lastMonthName = Carbon::now()->subMonth()->startOfMonth()->format('M');

        $revenueLastMonth = Order::where('status','!=', 'canceled')
                        ->whereDate('created_at','>=',$lastMonthStartDate)
                        ->whereDate('created_at','<=',$lastMonthEndDate)
                        ->sum('grand_total');

        // Last 30 days revenue
        $lastThirtyDaysStartDate = Carbon::now()->subDays(30)->format('Y-m-d');
        
        $revenueLastThirtyDays = Order::where('status','!=', 'canceled')
                        ->whereDate('created_at','>=',$lastThirtyDaysStartDate)
                        ->whereDate('created_at','<=',$currentDate)
                        ->sum('grand_total');

       return view('admin.dashboard',compact('totalOrders','totalProducts','totalCustomers','totalRevenue',
       'revenueThisMonth','revenueLastMonth','revenueLastThirtyDays','lastMonthName'));
    }
    public function logout () {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }
}
