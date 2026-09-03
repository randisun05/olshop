<?php

namespace Tests\Unit;

use App\Enums\ConversationStatus;
use App\Models\Conversation;
use App\Models\FaqEntry;
use App\Models\User;
use App\Services\ChatBotResponder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatBotResponderTest extends TestCase
{
    use RefreshDatabase;

    public function test_bot_replies_when_message_matches_a_faq_keyword(): void
    {
        FaqEntry::factory()->create([
            'question' => 'Bagaimana cara lacak pesanan?',
            'answer' => 'Buka menu Lacak Pesanan.',
            'keywords' => 'resi, lacak',
        ]);

        $conversation = Conversation::factory()->create(['status' => ConversationStatus::Open]);

        $reply = (new ChatBotResponder)->respond($conversation, 'Nomor resi saya berapa ya?');

        $this->assertNotNull($reply);
        $this->assertSame('Buka menu Lacak Pesanan.', $reply->body);
        $this->assertSame('Bot Toko', $reply->sender->name);
    }

    public function test_bot_does_not_reply_when_no_keyword_matches(): void
    {
        FaqEntry::factory()->create(['keywords' => 'resi, lacak']);

        $conversation = Conversation::factory()->create(['status' => ConversationStatus::Open]);

        $reply = (new ChatBotResponder)->respond($conversation, 'Halo, apa kabar toko?');

        $this->assertNull($reply);
    }

    public function test_bot_ignores_inactive_faq_entries(): void
    {
        FaqEntry::factory()->create(['keywords' => 'retur', 'is_active' => false]);

        $conversation = Conversation::factory()->create(['status' => ConversationStatus::Open]);

        $reply = (new ChatBotResponder)->respond($conversation, 'Saya mau retur barang.');

        $this->assertNull($reply);
    }

    public function test_bot_stops_replying_once_conversation_is_assigned_to_staff(): void
    {
        FaqEntry::factory()->create(['keywords' => 'resi, lacak']);

        $staff = User::factory()->create();
        $conversation = Conversation::factory()->create(['status' => ConversationStatus::Open, 'assigned_to' => $staff->id]);

        $reply = (new ChatBotResponder)->respond($conversation, 'Nomor resi saya berapa?');

        $this->assertNull($reply);
    }

    public function test_bot_picks_the_entry_with_the_most_matching_keywords(): void
    {
        FaqEntry::factory()->create([
            'answer' => 'Jawaban umum ongkir.',
            'keywords' => 'ongkir',
        ]);
        FaqEntry::factory()->create([
            'answer' => 'Jawaban spesifik ongkir ke Jakarta.',
            'keywords' => 'ongkir, jakarta, kirim',
        ]);

        $conversation = Conversation::factory()->create(['status' => ConversationStatus::Open]);

        $reply = (new ChatBotResponder)->respond($conversation, 'Berapa ongkir kirim ke jakarta?');

        $this->assertSame('Jawaban spesifik ongkir ke Jakarta.', $reply->body);
    }
}
