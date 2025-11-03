<?php 
use Illuminate\Support\Facades\Route;
use OpenAI\Laravel\Facades\OpenAI;

Route::get('/test-openai', function() {
    try {
        $response = OpenAI::models()->list();  
        return response()->json([
            'success' => true,
            'models_count' => count($response->data),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});
