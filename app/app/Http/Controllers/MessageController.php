<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Services\InAppNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Request $request, Conversation $conversation, InAppNotifier $notifier): RedirectResponse
    {
        $this->authorize('message', $conversation);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $message = $conversation->messages()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        $conversation->update(['last_message_at' => now()]);

        $conversation->loadMissing(['venueProfile', 'artistProfile', 'opportunity']);
        $recipients = $notifier->venueManagerIds($conversation->venueProfile)
            ->merge($notifier->artistManagerIds($conversation->artistProfile))
            ->reject(fn ($id) => (int) $id === (int) $request->user()->id)
            ->values();

        $notifier->notifyMany(
            $recipients,
            'message.created',
            'message:'.$message->id,
            'Nova mensagem',
            'Nova mensagem em “'.$conversation->opportunity->title.'”.',
            ['url' => route('conversations.show', $conversation), 'message_id' => $message->id],
        );

        return redirect()
            ->route('conversations.show', $conversation)
            ->with('status', 'message-sent');
    }
}
