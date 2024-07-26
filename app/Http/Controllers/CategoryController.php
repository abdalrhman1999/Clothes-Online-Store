<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Category;

class CategoryController extends Controller
{
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'parent_id' => 'numeric|exists:categories,id',
            'en_name' => 'required|string|max:255',
            'ar_name' => 'required|string|max:255',
        ]);
    }

    public function getCategories()
    {
        $categories = Category::whereNull('parent_id')
                    ->with('Subcategories')
                    ->get();

        return response()->json($categories, 200);
    }
    
    public function searchCategories(Request $request)
    {
        if (Auth::user()->permission != 1){
            return response()->json(['message'=>'Access Denied.'], 403);
        }
        
        $categories = Category::whereNull('parent_id')
                    ->where('en_name', 'LIKE', '%' . $request['query'] . '%')
                    ->orWhere('ar_name', 'LIKE', '%' . $request['query'] . '%')
                    ->with('Subcategories')
                    ->get();

        return response()->json($categories, 200);
    }

    public function getSubcategories()
    {
        if (Auth::user()->permission != 1){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $subcategories = Category::whereNotNull('parent_id')
                        ->with('Category', 'Subcategories')
                        ->whereHas('Category', function($q) {
                            $q->whereNull('parent_id');
                        })
                        ->get();

        return response()->json($subcategories, 200);
    }

    public function searchSubcategories(Request $request)
    {
        if (Auth::user()->permission != 1){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $categories = Category::whereNotNull('parent_id')
                    ->with('Category', 'Subcategories')
                    ->whereHas('Category', function($q) {
                        $q->whereNull('parent_id');
                    })
                    ->where('en_name', 'LIKE', '%' . $request['query'] . '%')
                    ->orWhere('ar_name', 'LIKE', '%' . $request['query'] . '%')
                    ->get();

        return response()->json($categories, 200);
    }

    public function getSubsubcategories()
    {
        if (Auth::user()->permission != 1){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $subcategories = Category::whereNotNull('parent_id')
                        ->with('Category')
                        ->whereHas('Category', function($q) {
                            $q->whereNotNull('parent_id');
                        })
                        ->get();

        return response()->json($subcategories, 200);
    }

    public function searchSubsubcategories(Request $request)
    {
        if (Auth::user()->permission != 1){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $categories = Category::whereNotNull('parent_id')
                    ->with('Category')
                    ->whereHas('Category', function($q) {
                        $q->whereNotNull('parent_id');
                    })
                    ->where('en_name', 'LIKE', '%' . $request['query'] . '%')
                    ->orWhere('ar_name', 'LIKE', '%' . $request['query'] . '%')
                    ->get();

        return response()->json($categories, 200);
    }

    public function getSubcategoriesOfCategory($id)
    {
        $category = Category::find($id);

        if(!$category){
            return response()->json(['errors' => 'There is no category with this id !'], 400);
        }

        $subcategories = $category->Subcategories;

        return response()->json($subcategories, 200);
    }

    public function addCategory(Request $request)
    {
        if (Auth::user()->permission != 1){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $validatedData = $this->validator($request->all());
        if ($validatedData->fails())  {
            return response()->json(['errors'=>$validatedData->errors()], 400);
        }

        $category = new Category;

        $category->en_name = $request['en_name'];
        $category->ar_name = $request['ar_name'];
        if ($request['parent_id'])
            $category->parent_id = $request['parent_id'];

        $category->save();

        return response()->json(['data' => $category], 201);
    }

    public function updateCategory($id, Request $request)
    {
        if (Auth::user()->permission != 1){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $category = Category::find($id);

        if(!$category){
            return response()->json(['errors' => 'There is no category with this id !'], 400);
        }

        $validatedData = Validator::make($request->all(),
            [
                'parent_id' => 'numeric|exists:categories,id',
                'en_name' => 'string|max:255',
                'ar_name' => 'string|max:255',
            ]
        );

        if($validatedData->fails()){
            return response()->json(["errors"=>$validatedData->errors()], 400);
        }

        if ($request['en_name'])
            $category->en_name = $request['en_name'];
        if ($request['ar_name'])
            $category->ar_name = $request['ar_name'];
        if ($request['parent_id'])
            $category->parent_id = $request['parent_id'];

        $category->save();

        return response()->json(['data' => $category], 200);
    }

    public function deleteCategory($id)
    {
        if (Auth::user()->permission != 1){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $category = Category::find($id);

        if(!$category){
            return response()->json(['errors' => 'There is no category with this id !'], 400);
        }

        if(count($category->Subcategories) > 0){
            return response()->json(['errors' => 'This category has subcategories !'], 400);
        }

        if(count($category->Products) > 0){
            return response()->json(['errors' => 'This category has products !'], 400);
        }

        $category->delete();
        
        return response()->json(['message' => "Category Deleted"], 200);
    }
}
