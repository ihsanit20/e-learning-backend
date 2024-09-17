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

    private function formatPhoneNumber($phone)
    {
        // যদি ফোন নাম্বার '+88' দিয়ে শুরু না হয়, তাহলে '+88' যুক্ত করুন
        if (!str_starts_with($phone, '+88')) {
            if (str_starts_with($phone, '88')) {
                $phone = '+' . $phone; // যদি '88' থাকে, '+' যুক্ত করুন
            } else {
                $phone = '+88' . $phone; // না থাকলে '+88' যুক্ত করুন
            }
        }

        return $phone;
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);
    
        // ফোন নাম্বার ফরম্যাট করুন
        $phone = $this->formatPhoneNumber($validatedData['phone']);
    
        $user = User::create([
            'name' => $validatedData['name'],
            'phone' => $phone,
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
    
        // ফোন নাম্বার ফরম্যাট করুন
        $credentials['phone'] = $this->formatPhoneNumber($credentials['phone']);
    
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
            'phone' => 'required|string',
        ]);
    
        // ফোন নাম্বার ফরম্যাট করুন
        $phone = $this->formatPhoneNumber($request->input('phone'));
    
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
    
        // ফোন নাম্বার ফরম্যাট করুন
        $phone = $this->formatPhoneNumber($request->input('phone'));
    
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

        $apiKey = env('BULKSMS_API_KEY');
        $senderId = env('BULKSMS_SENDER_ID');

        $message = "Your Ciademy OTP is $otp";
    
        $response = Http::post('http://bulksmsbd.net/api/smsapi', [
            'api_key'   => $apiKey,
            'senderid'  => $senderId,
            'number'    => $phone,
            'message'   => $message,
        ]);
    
        return response()->json([
            'message'   => $response->successful()
                ? 'OTP sent successfully'
                : 'Failed to send OTP',
            'response'  => $response->object(),
        ]);
    }
    

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:255',
            'otp' => 'required|string|max:4',
        ]);

        $phone = $request->input('phone');
        $otp = $request->input('otp');

        $otpRecord = Otp::where('phone', $phone)
                        ->where('otp', $otp)
                        ->where('expires_at', '>', now())
                        ->first();

        if ($otpRecord) {
            $otpRecord->delete();
            return response()->json(['message' => 'OTP verified successfully']);
        } else {
            return response()->json(['message' => 'Invalid OTP or it has expired'], 400);
        }
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:255',
            'new_password' => 'required|string|min:6|confirmed',
        ]);
    
        $phone = $request->input('phone');
        $newPassword = $request->input('new_password');

        $user = User::where('phone', $phone)->first();
        
        if ($user) {
            $user->password = Hash::make($newPassword);
            $user->save();
    
            return response()->json(['message' => 'Password reset successfully']);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
    }
}