<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\OtpCode;
use App\Mail\OtpMail;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Check if email exists in the database
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user) {
            return back()->withErrors([
                'email' => 'This email address is not registered. Please sign up first or check your email.',
            ])->withInput($request->only('email'));
        }

        // Try to authenticate with the credentials
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        // Email exists but password is wrong
        return back()->withErrors([
            'password' => 'Incorrect password. Please try again.',
        ]);
    }

    public function showSignup()
    {
        return view('auth.signup');
    }

    public function signup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect('/dashboard')->with('success', 'Account created successfully!');
    }

    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();
        
        if ($user) {
            // Generate and send OTP
            $otpRecord = OtpCode::generateOtp($request->email);
            
            try {
                Mail::to($request->email)->send(new OtpMail($otpRecord->otp_code, $request->email));
                
                return response()->json([
                    'exists' => true,
                    'message' => 'OTP sent successfully to your email!'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'exists' => true,
                    'error' => 'Failed to send OTP. Please try again.'
                ], 500);
            }
        }
        
        return response()->json([
            'exists' => false,
            'message' => 'This email address is not registered in our system.'
        ]);
    }

    public function recover(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        // Check if email exists in the database
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return back()->withErrors([
                'email' => 'This email address is not registered in our system.',
            ]);
        }

        // Verify OTP
        if (!OtpCode::verifyOtp($request->email, $request->otp)) {
            return back()->withErrors([
                'otp' => 'Invalid or expired OTP code. Please try again.',
            ]);
        }

        // OTP verified successfully
        return redirect('/login')->with('success', 'OTP verified successfully! You can now log in to your account.');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $updateData['profile_picture'] = $filename;
        }

        // Only update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return redirect('/profile')->with('success', 'Profile updated successfully!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
