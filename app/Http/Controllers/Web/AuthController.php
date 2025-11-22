<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    /**
     * Hiển thị trang đăng nhập
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Xử lý đăng nhập
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        try {
            $response = Http::post(url('/api/login'), [
                'email' => $request->email,
                'password' => $request->password
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Lưu token và user vào localStorage (sẽ được xử lý bằng JavaScript)
                session(['jwt_token' => $data['access_token']]);
                session(['user' => $data['user']]);
                
                return redirect()->route('home')->with('success', 'Đăng nhập thành công!');
            } else {
                return back()->withErrors(['email' => $response->json()['message'] ?? 'Đăng nhập thất bại']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Có lỗi xảy ra. Vui lòng thử lại.']);
        }
    }

    /**
     * Hiển thị trang đăng ký
     */
    public function showRegister()
    {
        $universities = \App\Models\University::all();
        return view('auth.register', compact('universities'));
    }

    /**
     * Xử lý đăng ký
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'required|string|max:15',
            'university_id' => 'nullable|exists:universities,id'
        ]);

        try {
            $response = Http::post(url('/api/register'), $request->all());

            if ($response->successful()) {
                $data = $response->json();
                
                session(['jwt_token' => $data['access_token']]);
                session(['user' => $data['data']]);
                
                return redirect()->route('home')->with('success', 'Đăng ký thành công!');
            } else {
                $errors = $response->json();
                return back()->withErrors($errors);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Có lỗi xảy ra. Vui lòng thử lại.']);
        }
    }

    /**
     * Đăng xuất
     */
    public function logout()
    {
        // Clear session if any
        session()->forget(['jwt_token', 'user']);
        
        // Return JSON response for API calls, or redirect for web
        if (request()->expectsJson()) {
            return response()->json(['message' => 'Đã đăng xuất thành công']);
        }
        
        return redirect()->route('home')->with('success', 'Đã đăng xuất!');
    }
}

