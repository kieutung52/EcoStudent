<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProfileController extends Controller
{
    /**
     * Hiển thị trang profile
     */
    public function index()
    {
        return view('profile.index');
    }

    /**
     * Cập nhật profile
     */
    public function update(Request $request)
    {
        $token = session('jwt_token') ?? $request->header('Authorization');
        
        if (!$token) {
            return redirect()->route('auth.login');
        }

        $token = str_replace('Bearer ', '', $token);

        try {
            $response = Http::withToken($token)->put(url('/api/profile'), $request->all());

            if ($response->successful()) {
                $data = $response->json();
                session(['user' => $data['data']]);
                return back()->with('success', 'Cập nhật profile thành công!');
            } else {
                return back()->withErrors(['error' => $response->json()['message'] ?? 'Cập nhật thất bại']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Có lỗi xảy ra. Vui lòng thử lại.']);
        }
    }
}

