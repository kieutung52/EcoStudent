<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rule;
use Illuminate\Http\Request;

class RuleController extends Controller
{
    public function index()
    {
        $rules = Rule::where('is_active', true)
            ->orderBy('order')
            ->orderBy('created_at')
            ->get();

        return response()->json($rules);
    }

    public function indexAdmin()
    {
        $rules = Rule::orderBy('order')
            ->orderBy('created_at')
            ->get();

        return response()->json($rules);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean'
        ]);

        $rule = Rule::create([
            'title' => $request->title,
            'content' => $request->content,
            'order' => $request->order ?? 0,
            'is_active' => $request->is_active ?? true
        ]);

        return response()->json([
            'message' => 'Tạo luật lệ thành công',
            'data' => $rule
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $rule = Rule::findOrFail($id);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'order' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean'
        ]);

        $rule->update($request->only(['title', 'content', 'order', 'is_active']));

        return response()->json([
            'message' => 'Cập nhật luật lệ thành công',
            'data' => $rule
        ]);
    }

    public function destroy($id)
    {
        $rule = Rule::findOrFail($id);
        $rule->delete();

        return response()->json(['message' => 'Đã xóa luật lệ']);
    }
}

