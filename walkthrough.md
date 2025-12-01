# Walkthrough - OTP Verification & Seller Profile

## Changes Implemented

### 1. OTP Verification
- **Database**: Created `otps` table to store OTP codes.
- **Backend**:
  - Created `App\Services\OtpService` to handle OTP generation and sending via Resend.
  - Updated `AuthController` (API & Web) to support registration flow with OTP.
  - Added `verify-otp` and `resend-otp` endpoints.
- **Frontend**:
  - Created `resources/views/auth/verify-otp.blade.php` for entering the code.
  - Updated `register.blade.php` to redirect to the verification page instead of logging in immediately.

### 2. Seller Profile
- **Backend**:
  - Added `showPublicProfile` to `Api\AuthController` (for API consumers).
  - Added `showSellerProfile` to `Web\HomeController` (for Blade views).
- **Frontend**:
  - Created `resources/views/profile/seller.blade.php` to display seller information and their posts.
  - Updated `resources/views/partials/post-card.blade.php` to link the user's name and avatar to their profile.

## Verification Steps

### OTP Flow
1.  Go to `/register`.
2.  Fill in the form and submit.
3.  You should be redirected to `/verify-otp`.
4.  Check your email (configured with Resend) for the code.
5.  Enter the code.
6.  You should be redirected to the home page and logged in.

### Seller Profile
1.  Go to the home page.
2.  Click on a user's name or avatar on any post card.
3.  You should be taken to `/seller/{id}`.
4.  You should see the seller's info (name, university, join date) and their list of posts.
5.  Verify that you cannot edit their profile (read-only).

## Configuration
- Added `RESEND_API_KEY` to `.env`.
