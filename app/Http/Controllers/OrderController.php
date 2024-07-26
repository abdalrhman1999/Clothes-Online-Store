<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;

class OrderController extends Controller
{
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'paymenet_method' => 'required|string',
            'address' => 'required|string',
        ]);
    }

    public function getOrders()
    {
        $orders = Order::with('User', 'Products')->get();

        return response()->json($orders, 200);
    }

    public function getProductsOfOrder($id)
    {
        $order = Order::find($id);

        if(!$order){
            return response()->json(['errors' => 'There is no order with this id !'], 400);
        }

        return response()->json($order->Products, 200);
    }

    public function changeOrderState($id, Request $request)
    {
        if (Auth::user()->permission != 1){
            return response()->json(['message'=>'Access Denied.'], 403);
        }
        
        $order = Order::find($id);

        if(!$order){
            return response()->json(['errors' => 'There is no order with this id !'], 400);
        }

        $validatedData = Validator::make($request->all(),
            [
                'state' => 'required|string|in:Processed,On_The_Way,Delivered',
            ]
        );

        if($validatedData->fails()){
            return response()->json(["errors"=>$validatedData->errors()], 400);
        }

        $order->state = $request['state'];
        $order->save();

        return response()->json(['data' => 'State Changed'], 200);
    }

    public function addOrder(Request $request)
    {
        $user = Auth::user();

        if ($user->permission != 0){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $validatedData = $this->validator($request->all());
        if ($validatedData->fails())  {
            return response()->json(['errors'=>$validatedData->errors()], 400);
        }

        $carts = $user->Carts;

        if(count($carts) == 0){
            return response()->json(['message'=>'Your cart is empty!'], 400);
        }

        $productWithPivot = [];
        $cartWithPivot = [];
        $total_price = 0.0;

        for($i = 0; $i < count($carts); $i += 1){
            $cart = $carts[$i];
            $total_price += $cart->price * $cart->pivot->quantity;
            $productWithPivot[$cart->id] = ['quantity' => $cart->pivot->quantity];
            $cartWithPivot[$cart->id] = ['quantity' => $cart->pivot->quantity, 'state' => 1];
        }

        $order = new Order;

        $order->user_id = $user->id;
        $order->total_price = $total_price;
        $order->paymenet_method = $request['paymenet_method'];
        $order->address = $request['address'];

        $order->save();

        $order->Products()->attach($productWithPivot);
        $user->Carts()->sync($cartWithPivot);

        return response()->json(['data' => $order], 201);
    }
}
