<?php

use App\Http\Controllers\DocumentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user = \App\Models\User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => $validated['password'],
    ]);

    $token = $user->createToken('scanner-app')->plainTextToken;
    return response()->json(['user' => $user, 'token' => $token]);
});

Route::post('/login', function (Request $request) {
    $validated = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (!\Illuminate\Support\Facades\Auth::attempt($validated)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $user = \Illuminate\Support\Facades\Auth::user();
    $token = $user->createToken('scanner-app')->plainTextToken;
    return response()->json(['user' => $user, 'token' => $token]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $r) => $r->user());
    Route::apiResource('documents', DocumentController::class);
});
