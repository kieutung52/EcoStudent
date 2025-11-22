<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\University;
use Illuminate\Http\Request;

class UniversityController extends Controller
{
    // Public: Lấy danh sách trường để fill vào dropdown
    public function index()
    {
        $universities = University::all();
        return response()->json($universities);
    }

    // Admin Only: Thêm trường mới
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:universities',
            'code' => 'required|string|unique:universities',
            'address' => 'required|string'
        ]);

        $university = University::create($request->all());

        return response()->json([
            'message' => 'Thêm trường đại học thành công',
            'data' => $university
        ], 201);
    }
    
    // Admin Only: Cập nhật
    public function update(Request $request, $id)
    {
        $university = University::findOrFail($id);
        $university->update($request->all());
        return response()->json(['message' => 'Cập nhật thành công', 'data' => $university]);
    }

    // Admin Only: Xóa
    public function destroy($id)
    {
        University::destroy($id);
        return response()->json(['message' => 'Xóa thành công']);
    }
}
