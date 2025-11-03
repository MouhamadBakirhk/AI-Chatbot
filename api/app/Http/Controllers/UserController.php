<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UserController extends Controller
{
    // List all users
    public function index() {
        $users = User::all();
        return response()->json($users);
    }

    // Get single user
    public function show($id) {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    // Register new user
    public function register(Request $request) {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'user' => $user
        ]);
    }

    // Login
    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials'], 401);
        }

        // قراءة المفتاح من env والتأكد منه
        $secret = env('JWT_SECRET');
        if (!$secret || !is_string($secret)) {
            return response()->json(['success' => false, 'message' => 'JWT_SECRET missing or invalid'], 500);
        }

        // إنشاء payload
        $payload = [
            'sub' => $user->id,
            'email' => $user->email,
            'iat' => time(),
            'exp' => time() + 3600 // صلاحية ساعة
        ];

        $token = JWT::encode($payload, $secret, 'HS256');

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    // Current logged-in user
    public function me(Request $request) {
        return response()->json($request->auth_user ?? null);
    }
}
