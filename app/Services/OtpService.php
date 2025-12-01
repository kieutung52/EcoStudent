<?php

namespace App\Services;

use App\Models\Otp;
use App\Models\User;
use Resend\Resend;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;

class OtpService
{
    protected $resend;

    public function __construct()
    {
        $apiKey = env('RESEND_API_KEY');
        $this->resend = Resend::client($apiKey);
    }

    public function generateAndSendOtp(User $user, $type = 'verification')
    {
        // Invalidate old OTPs
        Otp::where('user_id', $user->id)->where('type', $type)->delete();

        // Generate new OTP
        $otpCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Save to DB
        Otp::create([
            'user_id' => $user->id,
            'otp_code' => $otpCode,
            'type' => $type,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // Render Email Content
        $htmlContent = View::make('emails.otp', ['otp' => $otpCode])->render();

        // Send Email via Resend
        try {
            $this->resend->emails->send([
                'from' => 'onboarding@resend.dev', // Use verified domain if available, else testing domain
                'to' => 'kieuthanhtung0502@gmail.com',
                'subject' => 'Your Verification Code',
                'html' => $htmlContent,
            ]);
            return true;
        } catch (\Exception $e) {
            // Log error
            \Log::error('Resend Error: ' . $e->getMessage());
            return false;
        }
    }

    public function verifyOtp(User $user, $otpCode, $type = 'verification')
    {
        $otp = Otp::where('user_id', $user->id)
            ->where('type', $type)
            ->where('otp_code', $otpCode)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($otp) {
            $otp->delete(); // Consume OTP
            return true;
        }

        return false;
    }
}
