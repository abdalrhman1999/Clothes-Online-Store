<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Product;

class CartController extends Controller
{
    public function getCarts()
    {
        if (Auth::user()->permission != 0){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $carts = User::find(Auth::user()->id)->Carts;

        return response()->json($carts, 200);
    }

    public function searchCarts(Request $request)
    {
        if (Auth::user()->permission != 0){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $carts = [];
        $all_carts = User::find(Auth::user()->id)->Carts;

        for($i = 0; $i < count($all_carts); $i += 1){
            if(strpos(strtolower($all_carts[$i]->en_name), strtolower($request['query'])) !== false or
             strpos(strtolower($all_carts[$i]->ar_name), strtolower($request['query'])) !== false){
                $carts[] = $all_carts[$i];
            }
        }

        return response()->json($carts, 200);
    }

    public function addCart($id, Request $request)
    {
        $user = Auth::user();

        if ($user->permission != 0){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $validatedData = Validator::make($request->all(),
            [
                'quantity' => 'numeric|min:1',
            ]
        );

        if($validatedData->fails()){
            return response()->json(["errors"=>$validatedData->errors()], 400);
        }

        $product = Product::find($id);
        $product->quantity -= $request['quantity'];
        $product->sold += $request['quantity'];
        $product->save();

        $cart = DB::table('carts')->where([['user_id', '=', $user->id], ['product_id', '=', $id]])->first();

        $productWithPivot = [];

        if($cart){
            $productWithPivot[$id] = ['quantity' => $cart->quantity + $request['quantity']];
            $user->Carts()->detach($id);
        }else{
            $productWithPivot[$id] = ['quantity' => $request['quantity']];
        }
        $user->Carts()->attach($productWithPivot);
        
        return response()->json(['data' => 'Cart Added'], 201);
    }

    public function updateCart($id, Request $request)
    {
        $user = Auth::user();

        if ($user->permission != 0){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $validatedData = Validator::make($request->all(),
            [
                'quantity' => 'required|numeric|min:1',
            ]
        );

        if($validatedData->fails()){
            return response()->json(["errors"=>$validatedData->errors()], 400);
        }

        $cart = DB::table('carts')->where([['user_id', '=', $user->id], ['product_id', '=', $id]])->first();

        $product = Product::find($id);
        $product->quantity += $cart->quantity;
        $product->sold -= $cart->quantity;
        $product->quantity -= $request['quantity'];
        $product->sold += $request['quantity'];
        $product->save();

        $productWithPivot = [];
        $productWithPivot[$id] = ['quantity' => $request['quantity']];

        $user->Carts()->sync($productWithPivot);

        return response()->json(['data' => 'Cart Updated'], 201);
    }

    public function deleteCart($id)
    {
        $user = Auth::user();

        if ($user->permission != 0){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $cart = DB::table('carts')->where([['user_id', '=', $user->id], ['product_id', '=', $id]])->first();

        $product = Product::find($id);
        $product->quantity += $cart->quantity;
        $product->sold -= $cart->quantity;
        $product->save();

        $user->Carts()->detach($id);

        return response()->json(['data' => 'Cart Deleted'], 200);
    }
}
