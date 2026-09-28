<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ContactMessageStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Mail\ContactAutoReply;
use App\Mail\ContactReceived;
use App\Models\ContactMessage;
use App\Models\Setting;
use App\Services\Turnstile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
    public function store(StoreContactMessageRequest $request, Turnstile $turnstile): JsonResponse
    {
        $ip = $request->validated('visitor_ip');

        if (! $turnstile->verify($request->validated('turnstile_token'), $ip)) {
            throw ValidationException::withMessages(['turnstile' => 'Security check failed. Please try again.']);
        }

        $message = DB::transaction(function () use ($request, $ip) {
            $message = ContactMessage::create([
                ...$request->safe()->only(['name', 'email', 'company', 'phone', 'need', 'budget', 'timeline', 'message', 'service_slug']),
                // Never store the raw IP: a keyed hash is enough for abuse handling.
                'ip_hash' => $ip ? hash_hmac('sha256', $ip, (string) config('app.key')) : null,
                'user_agent' => $request->validated('user_agent'),
                'status' => ContactMessageStatus::New,
            ]);

            $request->uploads()->each(fn ($upload) => $upload->update(['contact_message_id' => $message->id, 'expires_at' => null]));

            return $message;
        });

        $owner = config('portfolio.mail_to') ?: Setting::current()->email;
        if (filled($owner)) {
            Mail::to($owner)->queue(new ContactReceived($message));
        }
        Mail::to($message->email)->queue(new ContactAutoReply($message));

        return response()->json(['data' => ['received' => true]], 201);
    }
}
