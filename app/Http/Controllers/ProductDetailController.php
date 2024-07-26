<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\ProductDetail;
use App\Models\Product;

class ProductDetailController extends Controller
{
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'product_id' => 'required|numeric|exists:products,id',
            'en_title' => 'required|string|max:255',
            'ar_title' => 'required|string|max:255',
            'en_description' => 'required|string',
            'ar_description' => 'required|string',
        ]);
    }

    public function addProductDetail(Request $request)
    {
        if (Auth::user()->permission != 1){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $validatedData = $this->validator($request->all());
        if ($validatedData->fails())  {
            return response()->json(['errors'=>$validatedData->errors()], 400);
        }

        $detail = new ProductDetail;

        $detail->product_id = $request['product_id'];
        $detail->en_title = $request['en_title'];
        $detail->ar_title = $request['ar_title'];
        $detail->en_description = $request['en_description'];
        $detail->ar_description = $request['ar_description'];

        $detail->save();

        return response()->json(['data' => $detail], 201);
    }

    public function updateProductDetail($id, Request $request)
    {
        if (Auth::user()->permission != 1){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $detail = ProductDetail::find($id);

        if(!$detail){
            return response()->json(['errors' => 'There is no product detail with this id !'], 400);
        }

        $validatedData = Validator::make($request->all(),
            [
                'en_title' => 'string|max:255',
                'ar_title' => 'string|max:255',
                'en_description' => 'string',
                'ar_description' => 'string',
            ]
        );

        if($validatedData->fails()){
            return response()->json(["errors"=>$validatedData->errors()], 400);
        }

        if ($request['en_title'])
            $detail->en_title = $request['en_title'];
        if ($request['ar_title'])
            $detail->ar_title = $request['ar_title'];
        if ($request['en_description'])
            $detail->en_description = $request['en_description'];
        if ($request['ar_description'])
            $detail->ar_description = $request['ar_description'];

        $detail->save();

        return response()->json(['data' => $detail], 200);
    }

    public function deleteProductDetail($id)
    {
        if (Auth::user()->permission != 1){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $detail = ProductDetail::find($id);

        if(!$detail){
            return response()->json(['errors' => 'There is no product detail with this id !'], 400);
        }

        $detail->delete();
        
        return response()->json(['message' => "Product Detail Deleted"], 200);
    }
}
