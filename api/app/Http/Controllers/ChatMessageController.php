<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatMessage;

class ChatMessageController extends Controller
{
     
    public function store(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
        ]);

        $message = ChatMessage::create($data);

        return response()->json($message, 201);
    }
     public function reply(Request $request)
{
    $userInput = $request->input('message');
    $userInputLower = strtolower($userInput);

    $messages = ChatMessage::all();

    $bestAnswer = null;
    $maxScore = 0;

    $userWords = preg_split('/\s+/', $userInputLower);

    foreach ($messages as $message) {
        $questionLower = strtolower($message->question);
        $questionWords = preg_split('/\s+/', $questionLower);

         
        $wordMatches = count(array_intersect($userWords, $questionWords));

         
        $levDistance = levenshtein($userInputLower, $questionLower);
        $levScore = max(0, 100 - $levDistance);  

         
        $score = $wordMatches * 10 + $levScore;

        if ($score > $maxScore) {
            $maxScore = $score;
            $bestAnswer = $message->answer;
        }

      
        if (strpos($questionLower, $userInputLower) !== false) {
            $bestAnswer = $message->answer;
            break;
        }
    }

    if (!$bestAnswer) {
        $bestAnswer = "Sorry, I couldn't find an answer to that question.";
    }

    return response()->json(['answer' => $bestAnswer]);
}


    
    public function index()
    {
        return response()->json(ChatMessage::latest()->get());
    }
}
