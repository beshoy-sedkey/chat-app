<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ChatContoller extends Controller
{
    public function chat()
    {

        $users = User::all();
        $onlineUsers = $users->filter(function ($user) {
            return Cache::has('user-online-' . $user->id) ;
        });
        $messages = Message::where(function ($query) {
            $userId = auth()->id();
            $query->where('sender_id', $userId)
                ->orWhere('recipient_id', $userId);
        })->get();


        return view('chat', compact('messages' , 'onlineUsers'));
    }


    public function sendMessage(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|integer|exists:users,id',
            'content' => 'required|string'
        ]);

        $message = new Message();
        $message->sender_id = auth()->id();
        $message->recipient_id = $request->recipient_id;
        $message->content = $request->content;
        $message->save();

        return back()->with('status', 'Message sent successfully!');
    }
}
