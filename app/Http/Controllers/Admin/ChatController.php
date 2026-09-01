<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ConversationStatus;
use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function index(Request $request): Response
    {
        $conversations = Conversation::query()
            ->with(['user', 'assignedTo'])
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('last_message_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Conversation $conversation) => [
                'id' => $conversation->id,
                'subject' => $conversation->subject,
                'customer' => $conversation->user->name,
                'assigned_to' => $conversation->assignedTo?->name,
                'status' => $conversation->status->value,
                'status_label' => $conversation->status->label(),
                'unread' => $conversation->unreadCountFor('staff'),
                'last_message_at' => $conversation->last_message_at?->toIso8601String(),
            ]);

        return Inertia::render('Admin/Chat/Index', [
            'conversations' => $conversations,
            'filters' => $request->only('status'),
        ]);
    }

    public function show(Request $request, Conversation $conversation): Response
    {
        $conversation->markReadBy('staff');

        return Inertia::render('Admin/Chat/Show', [
            'conversation' => $this->transform($conversation),
            'messages' => $this->transformMessages($conversation, $request->user()->id),
        ]);
    }

    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $validated = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);
        $message->setRelation('sender', $request->user());

        $conversation->update([
            'assigned_to' => $conversation->assigned_to ?? $request->user()->id,
            'last_message_at' => now(),
            'staff_read_at' => now(),
        ]);

        return response()->json([
            'message' => $this->transformMessages($conversation, $request->user()->id, collect([$message]))[0],
        ]);
    }

    public function poll(Request $request, Conversation $conversation): JsonResponse
    {
        $afterId = $request->integer('after_id');
        $messages = $conversation->messages()->where('id', '>', $afterId)->with('sender')->get();

        if ($messages->isNotEmpty()) {
            $conversation->markReadBy('staff');
        }

        return response()->json([
            'status_label' => $conversation->status->label(),
            'messages' => $this->transformMessages($conversation, $request->user()->id, $messages),
        ]);
    }

    public function close(Conversation $conversation): RedirectResponse
    {
        $conversation->update(['status' => ConversationStatus::Closed]);

        return back()->with('success', 'Percakapan ditutup.');
    }

    public function reopen(Conversation $conversation): RedirectResponse
    {
        $conversation->update(['status' => ConversationStatus::Open]);

        return back()->with('success', 'Percakapan dibuka kembali.');
    }

    /**
     * @return array<string, mixed>
     */
    private function transform(Conversation $conversation): array
    {
        return [
            'id' => $conversation->id,
            'subject' => $conversation->subject,
            'status' => $conversation->status->value,
            'status_label' => $conversation->status->label(),
            'customer' => $conversation->user->name,
            'order_number' => $conversation->order?->order_number,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function transformMessages(Conversation $conversation, int $viewerId, ?Collection $messages = null): array
    {
        $messages ??= $conversation->messages()->with('sender')->get();

        return $messages->map(fn (ChatMessage $message) => [
            'id' => $message->id,
            'body' => $message->body,
            'sender_name' => $message->sender->name,
            'is_mine' => $message->sender_id === $viewerId,
            'created_at' => $message->created_at->toIso8601String(),
        ])->all();
    }
}
