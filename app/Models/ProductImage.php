<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory;

    protected $table = 'product_images';

    protected $primaryKey = 'id';

    protected $fillable = [
        'product_id',
        'path',
    ];

    public function Product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
