<?php

namespace App\Http\Controllers\Customer;

use App\Enums\ConversationStatus;
use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Services\ChatBotResponder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function __construct(private readonly ChatBotResponder $chatBot) {}

    public function index(Request $request): Response
    {
        $conversations = $request->user()->conversations()
            ->withCount('messages')
            ->orderByDesc('last_message_at')
            ->paginate(10)
            ->through(fn (Conversation $conversation) => [
                'id' => $conversation->id,
                'subject' => $conversation->subject,
                'status_label' => $conversation->status->label(),
                'unread' => $conversation->unreadCountFor('customer'),
                'last_message_at' => $conversation->last_message_at?->toIso8601String(),
            ]);

        return Inertia::render('Customer/Chat/Index', [
            'conversations' => $conversations,
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Customer/Chat/Create', [
            'orders' => $request->user()->orders()->latest()->limit(20)->get(['id', 'order_number']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['nullable', Rule::exists('orders', 'id')->where('user_id', $request->user()->id)],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $conversation = $request->user()->conversations()->create([
            'order_id' => $validated['order_id'] ?? null,
            'subject' => $validated['subject'],
            'status' => ConversationStatus::Open,
            'customer_read_at' => now(),
            'last_message_at' => now(),
        ]);

        $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $validated['message'],
        ]);

        $this->chatBot->respond($conversation, $validated['message']);

        return redirect()->route('customer.chat.show', $conversation)->with('success', 'Pesan terkirim, tim kami akan segera membalas.');
    }

    public function show(Request $request, Conversation $conversation): Response
    {
        $this->authorizeConversation($request, $conversation);

        $conversation->markReadBy('customer');

        return Inertia::render('Customer/Chat/Show', [
            'conversation' => $this->transform($conversation),
            'messages' => $this->transformMessages($conversation, $request->user()->id),
        ]);
    }

    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeConversation($request, $conversation);

        $validated = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $validated['body'],
        ]);
        $message->setRelation('sender', $request->user());

        $conversation->update([
            'status' => ConversationStatus::Open,
            'last_message_at' => now(),
            'customer_read_at' => now(),
        ]);

        $botMessage = $this->chatBot->respond($conversation, $validated['body']);

        return response()->json([
            'messages' => $this->transformMessages(
                $conversation,
                $request->user()->id,
                collect(array_filter([$message, $botMessage]))
            ),
        ]);
    }

    public function poll(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeConversation($request, $conversation);

        $afterId = $request->integer('after_id');
        $messages = $conversation->messages()->where('id', '>', $afterId)->with('sender')->get();

        if ($messages->isNotEmpty()) {
            $conversation->markReadBy('customer');
        }

        return response()->json([
            'status_label' => $conversation->status->label(),
            'messages' => $this->transformMessages($conversation, $request->user()->id, $messages),
        ]);
    }

    private function authorizeConversation(Request $request, Conversation $conversation): void
    {
        abort_unless($conversation->user_id === $request->user()->id, 403);
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
