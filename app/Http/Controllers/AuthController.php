<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'phone' => $validatedData['phone'],
            'password' => Hash::make($validatedData['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['access_token' => $token, 'token_type' => 'Bearer']);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Your phone or password is incorrect'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['access_token' => $token, 'token_type' => 'Bearer']);
    }

    public function checkPhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string', // You may add additional validation rules here
        ]);

        $phone = $request->input('phone');

        // Check if the phone number exists in the database
        $isRegistered = User::where('phone', $phone)->exists();

        return response()->json(['isRegistered' => $isRegistered]);
    }
    
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully']);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
    
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'date_of_birth' => 'nullable|date',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'address' => 'nullable|string|max:255',
        ]);
    
        $user->name = $request->name;
        $user->phone = $request->phone;
        $user->date_of_birth = $request->date_of_birth;
        $user->email = $request->email;
        $user->address = $request->address;
    
        $user->save();
    
        return response()->json(['user' => $user], 200);
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:255',
        ]);

        $phone = $request->input('phone');

        // ইউজার এক্সিস্ট করে কিনা চেক করুন
        $user = User::where('phone', $phone)->first();
        
        if (!$user) {
            return response()->json(['message' => 'Phone number not registered'], 404);
        }

        // ৪ ডিজিটের OTP তৈরি করুন
        $otp = rand(1000, 9999);

        // OTP মেয়াদ সেট করা (৫ মিনিটের জন্য)
        $expiresAt = now()->addMinutes(5);

        // OTP ডাটাবেজে সংরক্ষণ করুন
        Otp::create([
            'phone' => $phone,
            'otp' => $otp,
            'expires_at' => $expiresAt,
        ]);

        // SMS API দিয়ে OTP পাঠানো
        $apiKey = 'J9wn09n3HqS72xl5prVi';
        $senderId = '8809617620253';
        $message = "Your OTP is $otp";

        $response = Http::get('http://bulksmsbd.net/api/smsapi', [
            'api_key' => $apiKey,
            'senderid' => $senderId,
            'number' => $phone,
            'message' => $message,
            'type' => 'text'
        ]);

        if ($response->successful()) {
            return response()->json(['message' => 'OTP sent successfully']);
        } else {
            return response()->json(['message' => 'Failed to send OTP'], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:255',
            'otp' => 'required|string|max:4',
        ]);

        $phone = $request->input('phone');
        $otp = $request->input('otp');

        // ডাটাবেজ থেকে OTP খুঁজে বের করুন
        $otpRecord = Otp::where('phone', $phone)
                        ->where('otp', $otp)
                        ->where('expires_at', '>', now()) // মেয়াদ শেষ না হওয়া পর্যন্ত
                        ->first();

        if ($otpRecord) {
            // OTP সফলভাবে যাচাই হলে, ডাটাবেজ থেকে মুছে ফেলুন বা প্রয়োজনীয় কাজ করুন
            $otpRecord->delete();
            return response()->json(['message' => 'OTP verified successfully']);
        } else {
            return response()->json(['message' => 'Invalid OTP or it has expired'], 400);
        }
    }


}