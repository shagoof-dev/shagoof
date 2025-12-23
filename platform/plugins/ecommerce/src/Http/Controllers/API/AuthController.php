<?php

namespace Botble\Ecommerce\Http\Controllers\API;

use Botble\Api\Http\Controllers\BaseApiController;
use Botble\Ecommerce\Models\Customer;
use Botble\Media\Facades\RvMedia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseApiController
{
    public function login(Request $request): JsonResponse
    {
        // 1. Validate request
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // 2. Authenticate using customer guard
        if (!Auth::guard('customer')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => __('Invalid email or password'),
            ], 401);
        }

        // 3. Get authenticated customer
        $customer = Auth::guard('customer')->user();

        // 4. Block inactive customers
        if (!$customer->status) {
            return response()->json([
                'success' => false,
                'message' => __('Your account is inactive'),
            ], 403);
        }

        // 5. Revoke old tokens
        $customer->tokens()->delete();

        // 6. Create token
        $token = $customer->createToken('customer-token')->plainTextToken;

        // 7. Success response
        return response()->json([
            'success' => true,
            'message' => __('Login successful'),
            'data' => [
                'token' => $token,
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'avatar' => RvMedia::getImageUrl($customer->avatar),
                ],
            ],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        // 1. Validate request
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:ec_customers,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        // 2. Create customer
        $customer = Customer::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => true,
        ]);

        // 3. Create token
        $token = $customer->createToken('customer-token')->plainTextToken;

        // 4. Response
        return response()->json([
            'success' => true,
            'message' => __('Registration successful'),
            'data' => [
                'token' => $token,
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'avatar' => RvMedia::getImageUrl($customer->avatar),
                ],
            ],
        ], 201);
    }

    public function currentUserDetails(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'welcome',
        ]);
        $customer = auth()->user();

        if (!$customer) {
            return response()->json([
                'error' => true,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        return response()->json([
            'error' => false,
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
            ],
        ]);
    }

}
