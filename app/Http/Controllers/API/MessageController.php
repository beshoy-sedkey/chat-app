<?php

namespace App\Http\Controllers\API;


use App\Models\User;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\MarkAsSeenRequest;
use App\Http\Requests\SendMessageRequst;
use Illuminate\Support\Facades\Auth;
use App\Http\Responses\ResponsesInterface;

class MessageController extends Controller
{
    protected $responder;

    public function __construct(ResponsesInterface $responder)
    {
        $this->responder = $responder;
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth('api')->user();
        $messages = Message::where('sender_id', $user->id)
            ->orWhere('recipient_id', $user->id)
            ->get();
        return $this->responder->respond(['messages'  => $messages]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SendMessageRequst $request)
    {
        $message = Message::create([
            'sender_id' => Auth::guard('api')->id(),
            'recipient_id' => $request->recipient_id,
            'content' => $request->content,
        ]);
        broadcast(new MessageSent($message))->toOthers();
        return $this->responder->respondCreated('Message Sent Successfully');
    }


    public function markAsSeen(MarkAsSeenRequest $request)
    {
        try {

            $user = Auth::guard('api')->user();
            DB::transaction(function () use ($request, $user) {
                $messageIds = $request->message_ids;
                Message::whereIn('id', $messageIds)
                    ->where('recipient_id', $user->id)
                    ->update(['seen' => true]);
            });
        } catch (\Throwable $th) {
            return $this->responder->respondWithError($th->getMessage());
        }
        return response()->json(['status' => 'success']);
    }
}
