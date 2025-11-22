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

/**
 * AuthController - Xử lý xác thực và phân quyền người dùng
 * 
 * JWT AUTHENTICATION FLOW CHI TIẾT:
 * =================================
 * 
 * 1. REGISTER (Đăng ký):
 *    --------------------
 *    a. Client gửi POST /api/register với: name, email, password, password_confirmation, phone, university_id
 *    b. Server validate dữ liệu
 *    c. Hash password bằng bcrypt (tự động qua User model cast 'hashed')
 *    d. Tạo User với role='USER', status='ACTIVE', is_active=true
 *    e. Tạo JWT token bằng JWTAuth::fromUser($user)
 *       - Token chứa: user_id, role, status, email (từ getJWTCustomClaims())
 *       - Token có thời gian hết hạn (TTL) được cấu hình trong config/jwt.php
 *    f. Trả về token và thông tin user
 *    g. Client lưu token (localStorage/sessionStorage) để dùng cho các request sau
 * 
 * 2. LOGIN (Đăng nhập):
 *    -------------------
 *    a. Client gửi POST /api/login với: email, password
 *    b. Server kiểm tra credentials bằng Auth::attempt()
 *       - Auth::attempt() tự động hash password và so sánh với DB
 *       - Nếu đúng, tạo session và attach user vào request
 *    c. Kiểm tra trạng thái tài khoản (is_active, status)
 *    d. Tạo JWT token bằng JWTAuth::fromUser($user)
 *    e. Trả về token và thông tin user
 * 
 * 3. AUTHENTICATED REQUESTS (Các request yêu cầu đăng nhập):
 *    ---------------------------------------------------------
 *    a. Client gửi request kèm token trong header:
 *       Authorization: Bearer {token}
 *    b. Middleware 'auth:api' (JWT) sẽ:
 *       - Extract token từ header Authorization
 *       - Verify token signature (kiểm tra chữ ký)
 *       - Verify token expiration (kiểm tra hết hạn)
 *       - Decode token để lấy user_id và claims
 *       - Load User từ database bằng user_id
 *       - Attach User vào request ($request->user())
 *    c. Controller có thể truy cập user qua $request->user() hoặc auth()->user()
 * 
 * 4. LOGOUT (Đăng xuất):
 *    --------------------
 *    a. Client gửi POST /api/logout với token trong header
 *    b. Server invalidate token bằng JWTAuth::invalidate()
 *       - Token bị đưa vào blacklist (nếu có cấu hình)
 *       - Token không thể sử dụng lại được
 *    c. Trả về thông báo thành công
 *    d. Client xóa token khỏi storage
 * 
 * 5. REFRESH TOKEN (Làm mới token):
 *    --------------------------------
 *    a. Khi token gần hết hạn, client có thể refresh
 *    b. Client gửi POST /api/refresh với token hiện tại
 *    c. Server verify token (có thể hết hạn nhưng vẫn trong refresh window)
 *    d. Tạo token mới với thời gian hết hạn mới
 *    e. Trả về token mới
 * 
 * PASSWORD HASHING:
 * ================
 * - Password được hash tự động khi tạo/update User nhờ cast 'hashed' trong User model
 * - Sử dụng bcrypt algorithm với cost factor 10 (mặc định Laravel)
 * - Hash::check($plainPassword, $hashedPassword) để so sánh khi login
 * - Password không bao giờ được lưu dạng plain text trong database
 */
class AuthController extends Controller
{
    /**
     * Đăng ký tài khoản mới
     * POST /api/register
     * 
     * Flow:
     * 1. Validate input (name, email, password, phone)
     * 2. Hash password (tự động qua User model)
     * 3. Tạo user với role='USER', status='ACTIVE'
     * 4. Tạo JWT token
     * 5. Trả về token và user info
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed', // Cần field password_confirmation
            'phone' => 'required|string|max:15',
            'university_id' => 'nullable|exists:universities,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Tạo user - password sẽ được hash tự động nhờ cast 'hashed' trong User model
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, // Sẽ được hash tự động
            'phone' => $request->phone,
            'university_id' => $request->university_id,
            'role' => 'USER', // Mặc định là USER
            'status' => 'ACTIVE', // Mặc định là ACTIVE
            'is_active' => true
        ]);

        // Tạo JWT token từ user
        // Token sẽ chứa: user_id, role, status, email (từ getJWTCustomClaims())
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Đăng ký thành công',
            'data' => $user->load('university'),
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl') * 60 // Thời gian hết hạn (giây)
        ], 201);
    }

    /**
     * Đăng nhập
     * POST /api/login
     * 
     * Flow:
     * 1. Validate email và password
     * 2. Auth::attempt() kiểm tra credentials (tự động hash và so sánh)
     * 3. Kiểm tra trạng thái tài khoản (is_active, status)
     * 4. Tạo JWT token
     * 5. Trả về token và user info
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        // Validate input
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Auth::attempt() sẽ:
        // - Tìm user theo email
        // - Hash password input và so sánh với password trong DB
        // - Nếu đúng, tạo session và attach user vào request
        if (!$token = Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Thông tin đăng nhập không chính xác'
            ], 401);
        }

        $user = Auth::user();

        // Kiểm tra trạng thái tài khoản
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

        // Tạo JWT token từ user đã authenticated
        $token = JWTAuth::fromUser($user);

        $response = [
            'message' => 'Đăng nhập thành công',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'user' => $user->load('university')
        ];

        // Nếu status là WARNING, thêm warning_message vào response
        if ($user->status === 'WARNING') {
            $response['warning_message'] = 'Tài khoản của bạn đã vi phạm nội quy nếu tiếp tục vi phạm thêm lỗi trong vòng 1 tuần nữa thì có thể sẽ bị khóa tài khoản';
        }

        return response()->json($response);
    }

    /**
     * Đăng xuất
     * POST /api/logout
     * 
     * Flow:
     * 1. Lấy token từ request (qua middleware auth:api)
     * 2. Invalidate token (đưa vào blacklist)
     * 3. Logout session (nếu có)
     * 4. Trả về thông báo thành công
     */
    public function logout(Request $request)
    {
        try {
            // Invalidate token - đưa token vào blacklist
            // Token này sẽ không thể sử dụng lại được
            JWTAuth::invalidate(JWTAuth::getToken());

            // Logout session (nếu có)
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

    /**
     * Làm mới token
     * POST /api/refresh
     * 
     * Flow:
     * 1. Lấy token hiện tại từ request
     * 2. Verify token (có thể hết hạn nhưng vẫn trong refresh window)
     * 3. Tạo token mới với thời gian hết hạn mới
     * 4. Trả về token mới
     */
    public function refresh()
    {
        try {
            // Refresh token - tạo token mới từ token hiện tại
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

    /**
     * Lấy thông tin profile của user hiện tại
     * GET /api/profile
     * 
     * Flow:
     * 1. Middleware auth:api đã load user vào request
     * 2. Trả về thông tin user kèm relationships
     */
    public function profile(Request $request)
    {
        // $request->user() được set bởi middleware auth:api sau khi verify JWT token
        $user = $request->user()->load('university');
        
        return response()->json($user);
    }

    /**
     * Cập nhật profile
     * PUT /api/profile
     */
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

    /**
     * Đổi mật khẩu
     * POST /api/change-password
     */
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

        // Kiểm tra mật khẩu hiện tại
        // Hash::check() so sánh plain text với hash trong DB
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Mật khẩu hiện tại không chính xác'
            ], 400);
        }

        // Cập nhật mật khẩu mới (sẽ được hash tự động)
        $user->password = $request->new_password;
        $user->save();

        return response()->json([
            'message' => 'Đổi mật khẩu thành công'
        ]);
    }

    /**
     * Danh sách users (Admin only)
     * GET /api/admin/users
     */
    public function listUsers(Request $request)
    {
        $query = User::with('university');

        // Filter theo status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter theo role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($users);
    }

    /**
     * Cập nhật trạng thái user (Admin only)
     * PUT /api/admin/users/{id}/status
     */
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

    /**
     * Khóa tài khoản user (Admin only)
     * PUT /api/admin/users/{id}/ban
     */
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
}
