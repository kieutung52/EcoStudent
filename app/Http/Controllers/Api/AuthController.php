<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    protected $otpService;

    public function __construct(\App\Services\OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed', 
            'phone' => 'required|string|max:15',
            'university_id' => 'nullable|exists:universities,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, 
            'phone' => $request->phone,
            'university_id' => $request->university_id,
            'role' => 'USER', 
            'status' => 'ACTIVE', 
            'is_active' => true
        ]);

        // Send OTP
        $this->otpService->generateAndSendOtp($user);

        return response()->json([
            'message' => 'Đăng ký thành công. Vui lòng kiểm tra email để lấy mã OTP.',
            'email' => $user->email
        ], 201);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Tài khoản đã được xác thực'], 200);
        }

        if ($this->otpService->verifyOtp($user, $request->otp)) {
            $user->email_verified_at = now();
            $user->save();

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'message' => 'Xác thực thành công',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => config('jwt.ttl') * 60,
                'user' => $user->load('university')
            ]);
        }

        return response()->json(['message' => 'Mã OTP không chính xác hoặc đã hết hạn'], 400);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Tài khoản đã được xác thực'], 400);
        }

        if ($this->otpService->generateAndSendOtp($user)) {
            return response()->json(['message' => 'Đã gửi lại mã OTP']);
        }

        return response()->json(['message' => 'Không thể gửi mã OTP. Vui lòng thử lại sau.'], 500);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$token = Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Thông tin đăng nhập không chính xác'
            ], 401);
        }

        $user = Auth::user();

        if (!$user->email_verified_at) {
            Auth::logout();
            return response()->json([
                'message' => 'Tài khoản chưa được xác thực email. Vui lòng xác thực.',
                'email' => $user->email,
                'requires_verification' => true
            ], 403);
        }

        if ($user->status === 'BANNED') {
            Auth::logout();
            return response()->json([
                'message' => 'Tài khoản của bạn đang bị khóa do phạm liên tiếp 2 lỗi trong vòng 1 tuần vui lòng liên hệ admin'
            ], 403);
        }

        if (!$user->is_active || $user->status === 'SHUT_DOWN') {
            Auth::logout();
            return response()->json([
                'message' => 'Tài khoản đã bị khóa hoặc không hoạt động'
            ], 403);
        }

        $token = JWTAuth::fromUser($user);

        $response = [
            'message' => 'Đăng nhập thành công',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => $user->load('university')
        ];

        if ($user->status === 'WARNING') {
            $response['warning_message'] = 'Tài khoản của bạn đã vi phạm nội quy nếu tiếp tục vi phạm thêm lỗi trong vòng 1 tuần nữa thì có thể sẽ bị khóa tài khoản';
        }

        return response()->json($response);
    }

    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            Auth::logout();

            return response()->json([
                'message' => 'Đăng xuất thành công'
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Không thể đăng xuất. Vui lòng thử lại.'
            ], 500);
        }
    }

    public function refresh()
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => config('jwt.ttl') * 60
            ]);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Không thể làm mới token. Vui lòng đăng nhập lại.'
            ], 401);
        }
    }

    public function profile(Request $request)
    {
        $user = $request->user()->load('university');
        
        return response()->json($user);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:15',
            'avatar' => 'sometimes|string',
            'university_id' => 'sometimes|nullable|exists:universities,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user->update($request->only(['name', 'phone', 'avatar', 'university_id']));

        return response()->json([
            'message' => 'Cập nhật profile thành công',
            'data' => $user->load('university')
        ]);
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Mật khẩu hiện tại không chính xác'
            ], 400);
        }

        $user->password = $request->new_password;
        $user->save();

        return response()->json([
            'message' => 'Đổi mật khẩu thành công'
        ]);
    }

    public function listUsers(Request $request)
    {
        $query = User::with('university');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($users);
    }

    public function updateUserStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'status' => 'required|in:ACTIVE,BANNED,WARNING,SHUT_DOWN',
            'is_active' => 'sometimes|boolean'
        ]);

        $user->update($request->only(['status', 'is_active']));

        return response()->json([
            'message' => 'Cập nhật trạng thái user thành công',
            'data' => $user->load('university')
        ]);
    }

    public function banUser($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'status' => 'BANNED',
            'is_active' => false
        ]);

        return response()->json([
            'message' => 'Đã khóa tài khoản',
            'data' => $user->load('university')
        ]);
    }

    public function showPublicProfile($id)
    {
        $user = User::with('university')->findOrFail($id);

        // Hide sensitive info
        $user->makeHidden(['email', 'phone', 'role', 'status', 'is_active', 'email_verified_at', 'created_at', 'updated_at']);
        
        // Get active posts
        $posts = \App\Models\Post::where('user_id', $id)
            ->where('status', 'APPROVED')
            ->with(['category', 'images'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'user' => $user,
            'posts' => $posts
        ]);
    }
}
