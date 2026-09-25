<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\ContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class ContactMessageController extends Controller
{
    /**
     * Store a message from the public contact form (the observer notifies the owner).
     */
    public function store(ContactMessageRequest $request): JsonResponse|RedirectResponse
    {
        $message = ContactMessage::query()->create([
            ...$request->safe()->only(['name', 'email', 'topic', 'message']),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'id' => 'msg_'.$message->id,
                'receivedAt' => $message->created_at?->toIso8601String(),
            ], 201);
        }

        return back()->with('success', 'Thanks — your message is on its way.');
    }
}
