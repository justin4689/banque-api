<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Account;

class UserController extends Controller
{
    // POST /api/users
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $account = Account::create([
            'user_id' => $user->id,
            'account_number' => strtoupper(Str::random(10)), // Génère un numéro unique
            'balance' => 0,
        ]);

        return response()->json([
            'user' => $user,
            'account' => $account,
        ], 201);
    }

    // GET /api/users/{userId}/accounts
    public function accounts($userId)
    {
        $user = User::findOrFail($userId);
        $accounts = $user->accounts;
        return response()->json($accounts);
    }

    // GET /api/profile
    public function profile(Request $request)
    {
        $user = $request->user();
        $accounts = $user->accounts()->get(['account_number', 'balance']);
        return response()->json([
            'name' => $user->name,
            'accounts' => $accounts,
        ]);
    }
}
