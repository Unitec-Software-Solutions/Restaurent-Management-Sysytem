<?php

namespace App\Http\Controllers;

use App\Models\CustomerProfile;
use App\Models\CustomerPreference;
use App\Models\CustomerAuthenticationMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CustomerController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customer_authentication_methods',
            'phone_number' => 'required|string|max:20|unique:customer_authentication_methods',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Create customer profile
        $profile = CustomerProfile::create([
            'name' => $request->name,
        ]);

        // Create authentication method
        $authMethod = CustomerAuthenticationMethod::create([
            'customer_profile_id' => $profile->id,
            'provider' => 'email',
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
        ]);

        // Create default preferences
        $preferences = CustomerPreference::create([
            'customer_profile_id' => $profile->id,
            'email_notifications' => true,
            'sms_notifications' => true,
        ]);

        return response()->json([
            'message' => 'Customer registered successfully',
            'profile' => $profile,
            'auth_method' => $authMethod,
            'preferences' => $preferences,
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required_without:phone_number|string|email',
            'phone_number' => 'required_without:email|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $authMethod = CustomerAuthenticationMethod::where(function ($query) use ($request) {
            $query->where('email', $request->email)
                  ->orWhere('phone_number', $request->phone_number);
        })->first();

        if (!$authMethod || !Hash::check($request->password, $authMethod->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $authMethod->createToken('customer-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'profile' => $authMethod->customerProfile,
            'preferences' => $authMethod->customerProfile->preferences,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $profile = $user->customerProfile;

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:customer_authentication_methods,email,' . $user->id,
            'phone_number' => 'sometimes|string|max:20|unique:customer_authentication_methods,phone_number,' . $user->id,
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $profile->update($request->only(['name']));
        $user->update($request->only(['email', 'phone_number']));

        return response()->json([
            'message' => 'Profile updated successfully',
            'profile' => $profile->fresh(),
        ]);
    }

    public function updatePreferences(Request $request)
    {
        $user = $request->user();
        $preferences = $user->customerProfile->preferences;

        $validator = Validator::make($request->all(), [
            'dietary_restrictions' => 'sometimes|array',
            'favorite_dishes' => 'sometimes|array',
            'allergies' => 'sometimes|array',
            'preferred_language' => 'sometimes|string|max:10',
            'email_notifications' => 'sometimes|boolean',
            'sms_notifications' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $preferences->update($request->all());

        return response()->json([
            'message' => 'Preferences updated successfully',
            'preferences' => $preferences->fresh(),
        ]);
    }

    public function requestPasswordReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required_without:phone_number|string|email',
            'phone_number' => 'required_without:email|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $authMethod = CustomerAuthenticationMethod::where(function ($query) use ($request) {
            $query->where('email', $request->email)
                  ->orWhere('phone_number', $request->phone_number);
        })->first();

        if (!$authMethod) {
            return response()->json(['message' => 'No account found with the provided credentials'], 404);
        }

        // Generate and send OTP
        $otp = rand(100000, 999999);
        // TODO: Implement OTP sending logic

        return response()->json(['message' => 'OTP sent successfully']);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required_without:phone_number|string|email',
            'phone_number' => 'required_without:email|string',
            'otp' => 'required|string|size:6',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $authMethod = CustomerAuthenticationMethod::where(function ($query) use ($request) {
            $query->where('email', $request->email)
                  ->orWhere('phone_number', $request->phone_number);
        })->first();

        if (!$authMethod) {
            return response()->json(['message' => 'No account found with the provided credentials'], 404);
        }

        // TODO: Verify OTP
        // if ($request->otp !== $storedOtp) {
        //     return response()->json(['message' => 'Invalid OTP'], 401);
        // }

        $authMethod->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Password reset successfully']);
    }
} 