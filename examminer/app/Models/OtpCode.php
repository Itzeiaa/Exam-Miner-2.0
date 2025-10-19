<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OtpCode extends Model
{
    protected $fillable = [
        'email',
        'otp_code',
        'expires_at',
        'used'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean'
    ];

    public static function generateOtp($email)
    {
        // Delete any existing OTPs for this email
        self::where('email', $email)->delete();
        
        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Create new OTP record (expires in 5 minutes)
        return self::create([
            'email' => $email,
            'otp_code' => $otp,
            'expires_at' => Carbon::now()->addMinutes(5)
        ]);
    }

    public static function verifyOtp($email, $otp)
    {
        $otpRecord = self::where('email', $email)
            ->where('otp_code', $otp)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($otpRecord) {
            $otpRecord->update(['used' => true]);
            return true;
        }

        return false;
    }
}
