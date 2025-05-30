<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Events\MessageSent;
use App\Models\User;

class MessageController extends Controller
{
    public function show(Request $request, User $user)
    {
        return Message::where(function ($query) use ($request, $user) {
            $query->where('origin_id', $request->user_id)
                  ->where('destiny_id', $user->id);
        })->orWhere(function ($query) use ($request, $user) {
            $query->where('destiny_id', $request->user_id)
                  ->where('origin_id', $user->id);
        })->with(["origin", "destiny"])
        ->orderBy('created_at', 'desc')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'message' => 'required|string',
            'destiny_id' => 'required|exists:users,id',
        ]);

        $data['origin_id'] = $request->user_id;

        $message = Message::create($data);

        return response()->json($message, 201);
    }
}

