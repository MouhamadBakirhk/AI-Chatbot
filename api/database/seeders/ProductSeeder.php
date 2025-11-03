<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
           
        $products = [
            [
                'name' => 'Laptop',
                'description' => 'Powerful gaming laptop',
                'category' => 'Electronics',
                'price' => 1200,
                'quantity' => 5,
                'image_url' => '/image/lap.jpeg',
            ],
            [
                'name' => 'Smartphone',
                'description' => 'Latest smartphone 5G',
                'category' => 'Electronics',
                'price' => 699,
                'quantity' => 20,
                'image_url' => '/image/i1.jpeg',
            ],
            [
                'name' => 'T-Shirt 1',
                'description' => 'Comfortable cotton t-shirt',
                'category' => 'Clothes',
                'price' => 20,
                'quantity' => 50,
                'image_url' => '/image/t-shirt1.jpg',
            ],
            [
                'name' => 'T-Shirt 2',
                'description' => 'Comfortable cotton t-shirt',
                'category' => 'Clothes',
                'price' => 20,
                'quantity' => 50,
                'image_url' => '/image/t-shirt3.jpg',
            ],
            [
                'name' => 'Pantalon 1',
                'description' => 'Comfortable cotton pantalon',
                'category' => 'Clothes',
                'price' => 25,
                'quantity' => 50,
                'image_url' => '/image/pan1.webp',
            ],
            [
                'name' => 'Pantalon 2',
                'description' => 'Comfortable cotton pantalon',
                'category' => 'Clothes',
                'price' => 25,
                'quantity' => 50,
                'image_url' => '/image/pan2.webp',
            ],
            [
                'name' => 'Novel',
                'description' => 'Interesting novel to read',
                'category' => 'Books',
                'price' => 15,
                'quantity' => 30,
                'image_url' => '/image/book.jpeg',
            ],
            [
                'name' => 'Novel - The Startup',
                'description' => 'The Startup',
                'category' => 'Books',
                'price' => 15,
                'quantity' => 30,
                'image_url' => '/image/book1.jpg',
            ],
             [
                'name' => 'Pantalon 4',
                'description' => 'Comfortable cotton pantalon',
                'category' => 'Clothes',
                'price' => 25,
                'quantity' => 50,
                'image_url' => '/image/pan4.jpg',
            ],

            //{add any product similair for this.} 
        ];

        foreach ($products as $p) {
            \App\Models\Product::updateOrCreate(
                ['name' => $p['name']],  
                $p                        
            );
        }
    }
}
