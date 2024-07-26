<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class FavoriteController extends Controller
{
    public function getFavorites()
    {
        if (Auth::user()->permission != 0){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $favorites = User::find(Auth::user()->id)->Favorites;

        return response()->json($favorites, 200);
    }

    public function searchFavorites(Request $request)
    {
        if (Auth::user()->permission != 0){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $favorites = [];
        $all_favorites = User::find(Auth::user()->id)->Favorites;

        for($i = 0; $i < count($all_favorites); $i += 1){
            if(strpos(strtolower($all_favorites[$i]->en_name), strtolower($request['query'])) !== false or
             strpos(strtolower($all_favorites[$i]->ar_name), strtolower($request['query'])) !== false){
                $favorites[] = $all_favorites[$i];
            }
        }

        return response()->json($favorites, 200);
    }

    public function addFavorite($id)
    {
        $user = Auth::user();

        if ($user->permission != 0){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $user->Favorites()->attach([$id]);

        return response()->json(['data' => 'Favorite Added'], 201);
    }

    public function deleteFavorite($id)
    {
        $user = Auth::user();

        if ($user->permission != 0){
            return response()->json(['message'=>'Access Denied.'], 403);
        }

        $user->Favorites()->detach($id);

        return response()->json(['data' => 'Favorite Deleted'], 200);
    }
}
