<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            // Issue token - but we're primarily using stateful Sanctum matching the prompt
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
                'message' => 'Logged in successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid credentials',
            'errors' => ['email' => ['Invalid credentials']]
        ], 401);
    }

    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,judge,lawyer,clerk,client',
            'phone' => 'nullable|string',
        ];

        if ($request->role === 'lawyer') {
            $rules['bar_number'] = 'required|string';
        } elseif (in_array($request->role, ['judge', 'clerk'])) {
            $rules['court_id'] = 'required|string';
        }

        $validated = $request->validate($rules);
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);
        Auth::login($user);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
            'message' => 'Registered successfully'
        ], 201);
    }

    public function user(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
            'message' => 'User profile retrieved'
        ]);
    }

    public function index(Request $request)
    {
        $query = User::query();
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }
        return response()->json([
            'success' => true,
            'data' => $query->get(['id', 'name', 'role', 'email']),
            'message' => 'Users retrieved'
        ]);
    }

    public function logout(Request $request)
    {
        // For token
        if ($request->user() && method_exists($request->user()->currentAccessToken(), 'delete')) {
            $request->user()->currentAccessToken()->delete();
        }
        
        // For stateful web guard
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'data' => [],
            'message' => 'Logged out successfully'
        ]);
    }
}
