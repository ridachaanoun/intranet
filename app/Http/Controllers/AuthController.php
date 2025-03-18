<?php

namespace App\Http\Controllers;

use App\Models\Promotion;
use App\Models\User;
use Auth;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\HasApiTokens;
use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class AuthController extends Controller
{
    use AuthorizesRequests ;
    public function register(Request $request)
    {
        try {
            $this->authorize('create', User::class);
    
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6',
                'role' => 'required|in:admin,teacher,student',
            ]);
    
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
    
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            if ($user->role === 'student') {
                // Create related empty records
                $user->personalInfo()->create([]);
                $user->accountInfo()->create([]);
                $user->profiles()->create([]);
            }
    
            return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['error' => 'Unauthorized'], 403);
        } catch (Exception $e) {
            return response()->json(['error' => 'Registration failed'], 500);
        }
    }
    

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            if (!auth()->attempt($request->only('email', 'password'))) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $user = auth()->user();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            if (!method_exists($user, 'createToken')) {
                return response()->json(['error' => 'Passport is not properly set up'], 500);
            }

            $token = $user->createToken('authToken')->accessToken;

            $responseData = [
                'token' => $token,
                'user' => $user
            ];
    
            return response()->json($responseData);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return response()->json(['message' => 'Logged out successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Logout failed'], 500);
        }
    }

    public function User(Request $request){
        try {
            $user = Auth()->user();
            $responseData = [
                'user' => $user
            ];

            if ($user->role === 'student') {
                $responseData['user']['personal_info'] = $user->personalInfo;
                $responseData['user']['account_info'] = $user->accountInfo->promotion;
                $responseData['user']['profiles'] = $user->profiles;
            }

            return response()->json($responseData,200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Something went wrong',"e"=>$e->getMessage()], 500);
        }
    }
}
