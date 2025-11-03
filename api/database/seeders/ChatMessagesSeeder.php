<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChatMessage;
use App\Models\Product;

class ChatMessagesSeeder extends Seeder
{
    public function run(): void
    {
         ChatMessage::truncate();
        $products = Product::all();

        foreach ($products as $product) {
            $product_faqs = [
                ['question' => "How much is product {$product->name}?", 'answer' => "The price of product {$product->name} is \${$product->price}."],
                ['question' => "Is product {$product->name} available?", 'answer' => "Yes, we currently have {$product->quantity} units of {$product->name} in stock."],
                ['question' => "How can I buy {$product->name}?", 'answer' => "You can add {$product->name} to your cart and proceed to checkout to buy it."],
                ['question' => "Can I return {$product->name}?", 'answer' => "Yes, {$product->name} can be returned within 14 days of purchase according to our return policy."],
            ];

            foreach ($product_faqs as $faq) {
                ChatMessage::updateOrCreate(
                    ['question' => $faq['question']],
                    ['answer' => $faq['answer']]
                );
            }
        }

         
        $general_faqs = [
            ['question' => 'What products are available?', 'answer' => 'We currently have clothes, electronics, and toys available.'],
            ['question' => 'How can I pay?', 'answer' => 'You can pay using credit card or PayPal.'],
            ['question' => 'Do you have a return policy?', 'answer' => 'Yes, you can return any product within 14 days of purchase.'],
            ['question' => 'Where is my order?', 'answer' => 'You can track your order using the tracking number provided in your confirmation email.'],
            ['question' => 'How can I contact you?', 'answer' => 'You can reach us via phone: 71447501 or email: mouhamadbakir513@gmail.com.'],
            ['question' => 'What is your email?', 'answer' => 'Our email is mouhamadbakir513@gmail.com.'],
            ['question' => 'Can I call you?', 'answer' => 'Yes, you can call us at 71447501.'],
            ['question' => 'How do I reach support?', 'answer' => 'Contact us via phone: 71447501 or email: mouhamadbakir513@gmail.com.'],
            ['question' => 'Do you ship internationally?', 'answer' => 'Yes, we ship to most countries worldwide.'],
            ['question' => 'How long does delivery take?', 'answer' => 'Delivery usually takes 3-7 business days.'],
            ['question' => 'Can I change my shipping address?', 'answer' => 'Yes, you can update your shipping address before the order is shipped.'],
            ['question' => 'Do you offer discounts?', 'answer' => 'We occasionally offer discounts and promotions on our website.'],
            ['question' => 'How do I create an account?', 'answer' => 'Click on Register and fill in your details to create an account.'],
            ['question' => 'I forgot my password, what do I do?', 'answer' => 'Click on Forgot Password and follow the instructions to reset it.'],
            ['question' => 'Can I gift wrap my order?', 'answer' => 'Yes, gift wrapping is available at checkout for an extra fee.'],
            ['question' => 'Are your products authentic?', 'answer' => 'Yes, all our products are 100% authentic and genuine.'],
            ['question' => 'Do you provide invoices?', 'answer' => 'Yes, invoices are provided with every purchase.'],
            ['question' => 'Is my personal information safe?', 'answer' => 'Yes, we use secure encryption to protect your personal data.'],
        ];

        foreach ($general_faqs as $faq) {
            ChatMessage::updateOrCreate(
                ['question' => $faq['question']],
                ['answer' => $faq['answer']]
            );
        }
    }
}
