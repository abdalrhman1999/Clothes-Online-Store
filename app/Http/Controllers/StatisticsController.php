<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;

class StatisticsController extends Controller
{
    public function getAdminStatistics()
    {
        if (Auth::user()->permission != 1){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $num_users = User::where('permission', '=', 0)->count('id');

        $num_categories = Category::whereNull('parent_id')->count('id');

        $num_subcategories = Category::whereNotNull('parent_id')
                                ->with('Category')
                                ->whereHas('Category', function($q) {
                                    $q->whereNull('parent_id');
                                })
                                ->count('id');

        $num_subsubcategories = Category::whereNotNull('parent_id')
                                ->with('Category')
                                ->whereHas('Category', function($q) {
                                    $q->whereNotNull('parent_id');
                                })
                                ->count('id');

        $num_processed_orders = Order::where('state', '=', 'Processed')->count('id');

        $num_on_the_way_orders = Order::where('state', '=', 'On_The_Way')->count('id');

        $num_delivered_orders = Order::where('state', '=', 'Delivered')->count('id');

        $num_products = Product::count('id');

        $sum_orders = Order::sum('total_price');

        return response()->json([
                    'num_users' => $num_users,
                    'num_categories' => $num_categories,
                    'num_subcategories' => $num_subcategories,
                    'num_subsubcategories' => $num_subsubcategories,
                    'num_processed_orders' => $num_processed_orders,
                    'num_on_the_way_orders' => $num_on_the_way_orders,
                    'num_delivered_orders' => $num_delivered_orders,
                    'num_products' => $num_products,
                    'sum_orders' => $sum_orders,
                ], 200);
    }
}
