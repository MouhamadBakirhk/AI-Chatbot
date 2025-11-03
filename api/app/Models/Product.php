<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity',
        'image_url',
    ];

    public function carts()
{
    return $this->hasMany(Cart::class);
}
public function ratings()
{
    return $this->hasMany(Rating::class);
}

 
public function averageRating()
{
    return $this->ratings()->avg('rating');
}
}


