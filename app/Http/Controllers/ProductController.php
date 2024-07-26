<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;

class ProductController extends Controller
{
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'category_id' => 'required|numeric|exists:categories,id',
            'en_name' => 'required|string|max:255',
            'ar_name' => 'required|string|max:255',
            'image' => ['required', 'image','mimes:jpeg,jpg,png'],
            'images' => 'required|array|min:1',
            'images.*' => ['required', 'image','mimes:jpeg,jpg,png'],
            'price' => 'required|numeric',
            'quantity' => 'numeric|min:1',
            'en_description' => 'required|string',
            'ar_description' => 'required|string',
        ]);
    }

    public function storeImage($image)
    {
        // Get filename with extension
        $filenameWithExt = $image->getClientOriginalName();

        // Get just the filename
        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);

        // Get extension
        $extension = $image->getClientOriginalExtension();

        // Create new filename
        $filenameToStore = 'product_images/'.$filename.'_'.time().'.'.$extension;

        // Uplaod image
        $path = $image->storeAs('public/', $filenameToStore);

        return $filenameToStore;
    }

    public function getProducts()
    {
        if (Auth::user()->permission != 1){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $products = Product::with('Images', 'Categories')->get();

        return response()->json($products, 200);
    }

    public function searchProducts(Request $request)
    {
        if (Auth::user()->permission != 1){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $products = Product::where('en_name', 'LIKE', '%' . $request['query'] . '%')->orWhere('ar_name', 'LIKE', '%' . $request['query'] . '%')->with('Images', 'Categories')->get();

        return response()->json($products, 200);
    }

    public function getProduct($id)
    {
        $product = Product::with('Images', 'Categories', 'ProductDetails')->find($id);

        if(!$product){
            return response()->json(['errors' => 'There is no product with this id !'], 400);
        }

        return response()->json($product, 200);
    }

    public function getNewProducts()
    {
        $products = Product::latest()->take(10)->get();

        return response()->json($products, 200);
    }

    public function getFreqProducts()
    {
        $products = DB::table('order_product')
                    ->join('products', 'products.id', '=','order_product.product_id')
                    ->select('product_id as id', 'image', 'en_name', 'ar_name', 'price', DB::raw('count(*) as total'))
                    ->groupBy('product_id')
                    ->orderBy('total', 'DESC')
                    ->get();

        return response()->json($products, 200);
    }

    public function getProductsOfCategory($id)
    {
        $category = Category::find($id);

        if(!$category){
            return response()->json(['errors' => 'There is no category with this id !'], 400);
        }

        return response()->json($category->Products, 200);
    }

    public function searchProductsOfCategory($id, Request $request)
    {
        $category = Category::find($id);

        if(!$category){
            return response()->json(['errors' => 'There is no category with this id !'], 400);
        }

        $products = [];
        $all_products = $category->Products;
        
        for($i = 0; $i < count($all_products); $i += 1){
            if(strpos(strtolower($all_products[$i]->en_name), strtolower($request['query'])) !== false or
             strpos(strtolower($all_products[$i]->ar_name), strtolower($request['query'])) !== false){
                $products[] = $all_products[$i];
            }
        }

        return response()->json($products, 200);
    }

    public function addProduct(Request $request)
    {
        if (Auth::user()->permission != 1){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $validatedData = $this->validator($request->all());
        if ($validatedData->fails())  {
            return response()->json(['errors'=>$validatedData->errors()], 400);
        }

        $product = new Product;

        $product->en_name = $request['en_name'];
        $product->ar_name = $request['ar_name'];
        $product->en_description = $request['en_description'];
        $product->ar_description = $request['ar_description'];
        $product->price = $request['price'];
        if ($request['quantity'])
            $product->quantity = $request['quantity'];
        if ($request->hasFile('image'))
            $product->image = $this->storeImage($request->file('image'));

        $product->save();

        $category = Category::find($request['category_id']);
        $category_array = [];
        $category_array[] = $request['category_id'];

        while($category->parent_id){
            $category_array[] = $category->parent_id;
            $category = Category::find($category->parent_id);
        }

        $product->Categories()->attach($category_array);

        for ($i = 0; $i < count($request['images']); $i += 1) {
            $image = new ProductImage;
            $image->product_id = $product->id;
            $image->path = $this->storeImage($request['images'][$i]);
            $image->save();
        }

        return response()->json(['data' => $product], 201);
    }

    public function updateProduct($id, Request $request)
    {
        if (Auth::user()->permission != 1){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $product = Product::find($id);

        if(!$product){
            return response()->json(['errors' => 'There is no product with this id !'], 400);
        }

        $validatedData = Validator::make($request->all(),
            [
                'category_id' => 'numeric|exists:categories,id',
                'en_name' => 'string|max:255',
                'ar_name' => 'string|max:255',
                'image' => ['image','mimes:jpeg,jpg,png'],
                'images' => 'array|min:1',
                'images.*' => ['image','mimes:jpeg,jpg,png'],
                'price' => 'numeric',
                'quantity' => 'numeric',
                'en_description' => 'string',
                'ar_description' => 'string',
            ]
        );

        if($validatedData->fails()){
            return response()->json(["errors"=>$validatedData->errors()], 400);
        }

        if ($request['en_name'])
            $product->en_name = $request['en_name'];
        if ($request['ar_name'])
            $product->ar_name = $request['ar_name'];
        if ($request['en_description'])
            $product->en_description = $request['en_description'];
        if ($request['ar_description'])
            $product->ar_description = $request['ar_description'];
        if ($request['price'])
            $product->price = $request['price'];
        if ($request['quantity'])
            $product->quantity = $request['quantity'];
        if ($request->hasFile('image'))
            $product->image = $this->storeImage($request->file('image'));

        $product->save();

        if($request['category_id']){
            $category = Category::find($request['category_id']);
            $category_array = [];
            $category_array[] = $request['category_id'];
    
            while($category->parent_id){
                $category_array[] = $category->parent_id;
                $category = Category::find($category->parent_id);
            }
    
            $product->Categories()->sync($category_array);
        }

        if($request['images'] and count($request['images']) > 0){
            $images = $product->Images;
            for ($i = 0; $i < count($images); $i += 1) {
                $image = $images[$i];
                $image->delete();
            }
            for ($i = 0; $i < count($request['images']); $i += 1) {
                $image = new ProductImage;
                $image->product_id = $product->id;
                $image->path = $this->storeImage($request['images'][$i]);
                $image->save();
            }
        }

        return response()->json(['data' => $product], 200);
    }

    public function deleteProduct($id)
    {
        if (Auth::user()->permission != 1){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $product = Product::find($id);

        if(!$product){
            return response()->json(['errors' => 'There is no product with this id !'], 400);
        }

        $product->delete();

        return response()->json(['message' => "Product Deleted"], 200);
    }
}
