<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductDetail extends Model
{
    use HasFactory;

    protected $table = 'product_details';

    protected $primaryKey = 'id';

    protected $fillable = [
        'en_name',
        'ar_name',
        'en_description',
        'ar_description',
    ];

    public function Product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
