<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Support\Facades\Auth;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $primaryKey = 'id';

    protected $fillable = [
        'en_name',
        'ar_name',
        'image',
        'price',
        'quantity',
        'sold',
        'en_description',
        'ar_description',
    ];

    public function Categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product', 'product_id', 'category_id');
    }

    public function Images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function ProductDetails(): HasMany
    {
        return $this->hasMany(ProductDetail::class);
    }

    public function Orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_product', 'product_id', 'order_id')->withPivot('quantity');
    }

    public function UserFavorites(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites', 'product_id', 'user_id');
    }

    public function UserFavorite(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites', 'product_id', 'user_id')->where('user_id', '=', 2);
    }

    // public function FavouriteAttribute()
    // {
    //     if(Auth::check())
    //     {
    //         if($this->belongsToMany(User::class, 'favorites', 'product_id', 'user_id')->where('user_id', '=', Auth::user()->id)->get()->count() > 0)
    //         {
    //             return true;
    //         }
    //     }
    //     return false;
    // }

    public function UserCarts(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'carts', 'product_id', 'user_id')->withPivot('quantity', 'state')->where('state', '=', 0);
    }
}
