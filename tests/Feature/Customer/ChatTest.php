<?php

namespace Tests\Feature\Customer;

use App\Models\Conversation;
use App\Models\FaqEntry;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_start_a_new_conversation(): void
    {
        $customer = $this->createCustomerUser();

        $response = $this->actingAs($customer)->post(route('customer.chat.store'), [
            'subject' => 'Pertanyaan seputar produk',
            'message' => 'Apakah produk ini tersedia dalam warna lain?',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('conversations', ['user_id' => $customer->id, 'subject' => 'Pertanyaan seputar produk']);
        $this->assertDatabaseHas('chat_messages', ['body' => 'Apakah produk ini tersedia dalam warna lain?', 'sender_id' => $customer->id]);
    }

    public function test_customer_can_link_conversation_to_own_order(): void
    {
        $customer = $this->createCustomerUser();
        $order = Order::factory()->create(['user_id' => $customer->id]);

        $response = $this->actingAs($customer)->post(route('customer.chat.store'), [
            'order_id' => $order->id,
            'subject' => 'Status pengiriman',
            'message' => 'Kapan pesanan saya dikirim?',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('conversations', ['order_id' => $order->id]);
    }

    public function test_customer_cannot_link_conversation_to_someone_elses_order(): void
    {
        $customer = $this->createCustomerUser();
        $otherOrder = Order::factory()->create();

        $response = $this->actingAs($customer)->post(route('customer.chat.store'), [
            'order_id' => $otherOrder->id,
            'subject' => 'Coba',
            'message' => 'Coba akses pesanan orang lain.',
        ]);

        $response->assertSessionHasErrors('order_id');
    }

    public function test_customer_cannot_view_someone_elses_conversation(): void
    {
        $customer = $this->createCustomerUser();
        $conversation = Conversation::factory()->create();

        $response = $this->actingAs($customer)->get(route('customer.chat.show', $conversation));

        $response->assertForbidden();
    }

    public function test_customer_can_send_message_and_gets_json_response(): void
    {
        $customer = $this->createCustomerUser();
        $conversation = Conversation::factory()->create(['user_id' => $customer->id]);

        $response = $this->actingAs($customer)->postJson(route('customer.chat.message', $conversation), [
            'body' => 'Halo, ada update?',
        ]);

        $response->assertOk();
        $response->assertJsonPath('messages.0.body', 'Halo, ada update?');
        $response->assertJsonPath('messages.0.is_mine', true);
        $this->assertDatabaseHas('chat_messages', ['conversation_id' => $conversation->id, 'body' => 'Halo, ada update?']);
    }

    public function test_sending_message_reopens_a_closed_conversation(): void
    {
        $customer = $this->createCustomerUser();
        $conversation = Conversation::factory()->create(['user_id' => $customer->id, 'status' => 'closed']);

        $this->actingAs($customer)->postJson(route('customer.chat.message', $conversation), ['body' => 'Masih ada masalah.']);

        $this->assertSame('open', $conversation->fresh()->status->value);
    }

    public function test_bot_auto_replies_when_starting_a_conversation_with_a_matching_question(): void
    {
        FaqEntry::factory()->create(['answer' => 'Cek nomor resi di halaman Lacak Pesanan.', 'keywords' => 'resi, lacak']);

        $customer = $this->createCustomerUser();

        $response = $this->actingAs($customer)->post(route('customer.chat.store'), [
            'subject' => 'Tanya resi',
            'message' => 'Nomor resi saya berapa ya?',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('chat_messages', ['body' => 'Cek nomor resi di halaman Lacak Pesanan.']);
    }

    public function test_bot_auto_replies_via_send_message_endpoint(): void
    {
        FaqEntry::factory()->create(['answer' => 'Kami terima transfer bank dan Midtrans.', 'keywords' => 'bayar, pembayaran']);

        $customer = $this->createCustomerUser();
        $conversation = Conversation::factory()->create(['user_id' => $customer->id]);

        $response = $this->actingAs($customer)->postJson(route('customer.chat.message', $conversation), [
            'body' => 'Metode pembayaran apa saja?',
        ]);

        $response->assertOk();
        $response->assertJsonCount(2, 'messages');
        $response->assertJsonPath('messages.1.body', 'Kami terima transfer bank dan Midtrans.');
        $response->assertJsonPath('messages.1.is_mine', false);
    }

    public function test_poll_only_returns_messages_after_given_id(): void
    {
        $customer = $this->createCustomerUser();
        $conversation = Conversation::factory()->create(['user_id' => $customer->id]);
        $first = $conversation->messages()->create(['sender_id' => $customer->id, 'body' => 'Pesan pertama']);
        $conversation->messages()->create(['sender_id' => $customer->id, 'body' => 'Pesan kedua']);

        $response = $this->actingAs($customer)->getJson(route('customer.chat.poll', $conversation).'?after_id='.$first->id);

        $response->assertOk();
        $response->assertJsonCount(1, 'messages');
        $response->assertJsonPath('messages.0.body', 'Pesan kedua');
    }
}
