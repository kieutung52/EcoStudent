<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // Lấy danh sách các cuộc trò chuyện
    public function index()
    {
        $userId = Auth::id();
        // Lấy hội thoại mà user tham gia (là user_one hoặc user_two)
        $conversations = Conversation::where('user_one', $userId)
            ->orWhere('user_two', $userId)
            ->with(['lastMessage']) // Eager load tin nhắn cuối
            ->get();

        return response()->json($conversations);
    }

    // Gửi tin nhắn
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|string'
        ]);

        $senderId = Auth::id();
        $receiverId = $request->receiver_id;

        // Tìm hoặc tạo hội thoại
        $conversation = Conversation::where(function($q) use ($senderId, $receiverId) {
            $q->where('user_one', $senderId)->where('user_two', $receiverId);
        })->orWhere(function($q) use ($senderId, $receiverId) {
            $q->where('user_one', $receiverId)->where('user_two', $senderId);
        })->firstOrCreate([
            'user_one' => min($senderId, $receiverId),
            'user_two' => max($senderId, $receiverId)
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $senderId,
            'content' => $request->input('content'),
            'is_read' => false
        ]);

        return response()->json($message->load('sender:id,name,avatar'), 201);
    }

    /**
     * Lấy tin nhắn của một hội thoại
     * GET /api/conversations/{id}/messages
     */
    public function getMessages($id)
    {
        $userId = Auth::id();
        
        // Kiểm tra user có tham gia hội thoại này không
        $conversation = Conversation::where('id', $id)
            ->where(function($q) use ($userId) {
                $q->where('user_one', $userId)
                  ->orWhere('user_two', $userId);
            })
            ->firstOrFail();

        $messages = Message::where('conversation_id', $id)
            ->with('sender:id,name,avatar')
            ->orderBy('created_at', 'asc')
            ->get();

        // Đánh dấu tin nhắn là đã đọc
        Message::where('conversation_id', $id)
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }
}