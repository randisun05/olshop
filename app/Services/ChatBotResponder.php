<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\Conversation;
use App\Models\FaqEntry;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Balasan otomatis berbasis pencocokan kata kunci terhadap FaqEntry — bukan
 * AI/LLM sungguhan, cukup untuk pertanyaan umum (cara order, ongkir,
 * retur, dst) sebelum staf manusia sempat membalas. Berhenti otomatis
 * begitu percakapan sudah ditugaskan ke staf, supaya tidak menyela
 * percakapan manusia yang sedang berlangsung.
 */
class ChatBotResponder
{
    public function respond(Conversation $conversation, string $customerMessage): ?ChatMessage
    {
        if ($conversation->assigned_to !== null) {
            return null;
        }

        $entry = $this->findBestMatch($customerMessage);

        if (! $entry) {
            return null;
        }

        $message = $conversation->messages()->create([
            'sender_id' => $this->botUser()->id,
            'body' => $entry->answer,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return $message;
    }

    private function findBestMatch(string $customerMessage): ?FaqEntry
    {
        $normalized = mb_strtolower($customerMessage);

        $best = null;
        $bestScore = 0;

        foreach (FaqEntry::where('is_active', true)->orderBy('sort_order')->get() as $entry) {
            $score = 0;

            foreach ($entry->keywordList() as $keyword) {
                if (str_contains($normalized, $keyword)) {
                    $score++;
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $entry;
            }
        }

        return $best;
    }

    /**
     * Akun sistem untuk bot — tidak bisa login (is_active false, password
     * acak) supaya tidak jadi celah keamanan, tapi tetap valid sebagai
     * sender_id (FK ke users) tanpa perlu mengubah skema chat_messages.
     */
    private function botUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'bot@toko.internal'],
            [
                'name' => 'Bot Toko',
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
                'is_active' => false,
            ]
        );
    }
}
