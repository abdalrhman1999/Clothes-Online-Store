<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $primaryKey = 'id';

    protected $fillable = [
        'parent_id',
        'en_name',
        'ar_name',
    ];

    public function Subcategories(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function Category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function Products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_product', 'category_id', 'product_id');
    }
}
