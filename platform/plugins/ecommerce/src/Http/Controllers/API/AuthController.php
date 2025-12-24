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
        Auth::setUser($customer);

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
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'status' => $customer->status,
                    'is_vendor' => $customer->is_vendor,
                    'avatar' => RvMedia::getImageUrl($customer->avatar),
                ],
            ],
        ]);
    }

    # register customer #
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

        // 4. Response
        return response()->json([
            'success' => true,
            'message' => __('Registration successful'),
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'avatar' => RvMedia::getImageUrl($customer->avatar),
                ],
            ],
        ], 201);
    }

    # current logged user details #
    public function currentUserDetails(Request $request): JsonResponse
    {
        $customer = auth()->user();
        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'status' => $customer->status,
                'is_vendor' => $customer->is_vendor,
                'avatar' => RvMedia::getImageUrl($customer->avatar),
            ],
        ]);
    }


    # current logged user details #
    public function getCustomerDetailsByID(Request $request): JsonResponse
    {
        $request->validate([
            'cid' => [
                'required',
                'integer',
                'exists:ec_customers,id',
            ],
        ]);

        $customerDetails = Customer::find($request->cid);
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $customerDetails->id,
                'name' => $customerDetails->name,
                'phone' => $customerDetails->phone,
                'email' => $customerDetails->email,
                'status' => $customerDetails->status,
                'is_vendor' => $customerDetails->is_vendor,
                'avatar' => RvMedia::getImageUrl($customerDetails->avatar),
            ],
        ]);
    }

    /*
     * Delete authenticated customer account
     * @route POST /api/v1/ecommerce/customer/delete-account
     */
    public function deleteCustomerAccount(Request $request): JsonResponse
    {
        $customer = auth()->user();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => __('Unauthenticated.'),
            ], 401);
        }

        $request->validate([
            'password' => ['required', 'string'],
        ]);

        // Verify password
        if (!Hash::check($request->password, $customer->password)) {
            return response()->json([
                'success' => false,
                'message' => __('Invalid password'),
            ], 403);
        }

        $customer->tokens()->delete();
        Auth::guard('customer')->logout();
        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => __('Your account has been deleted successfully'),
        ]);
    }

    /*
     * Partially update authenticated customer details (PATCH)
     * @route POST /api/v1/ecommerce/customer/update-profile
     */
    public function updateCustomerDetails(Request $request): JsonResponse
    {
        // Get authenticated customer
        $customer = auth()->user();

        if (!$customer)
            return response()->json([
                'success' => false,
                'message' => __('Unauthenticated.'),
            ], 401);

        // Validate only sent fields
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                'unique:ec_customers,email,' . $customer->id,
            ],
            'password' => ['sometimes', 'string', 'min:6', 'confirmed'],
        ]);

        // Apply updates dynamically
        foreach ($data as $key => $value) {
            if ($key === 'password') {
                $customer->password = Hash::make($value);
            } else {
                $customer->{$key} = $value;
            }
        }

        $customer->save();

        // Response
        return response()->json([
            'success' => true,
            'message' => __('Profile updated successfully'),
            'data' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'status' => $customer->status,
                'is_vendor' => $customer->is_vendor,
                'avatar' => RvMedia::getImageUrl($customer->avatar),
            ],
        ]);
    }




}
