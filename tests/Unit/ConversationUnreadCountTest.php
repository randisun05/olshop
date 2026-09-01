<?php

namespace Tests\Unit;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConversationUnreadCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_unread_count_for_customer_only_counts_staff_messages_after_last_read(): void
    {
        $customer = User::factory()->create();
        $staff = User::factory()->create();
        $conversation = Conversation::factory()->create(['user_id' => $customer->id]);

        $conversation->messages()->create(['sender_id' => $customer->id, 'body' => 'Pesan saya sendiri']);
        $conversation->messages()->create(['sender_id' => $staff->id, 'body' => 'Balasan 1']);
        $conversation->messages()->create(['sender_id' => $staff->id, 'body' => 'Balasan 2']);

        $this->assertSame(2, $conversation->unreadCountFor('customer'));
        $this->assertSame(Conversation::unreadCountForCustomer($customer), 2);

        $conversation->markReadBy('customer');

        $this->assertSame(0, $conversation->fresh()->unreadCountFor('customer'));
        $this->assertSame(0, Conversation::unreadCountForCustomer($customer));
    }

    public function test_unread_count_for_staff_only_counts_customer_messages_after_last_read(): void
    {
        $customer = User::factory()->create();
        $conversation = Conversation::factory()->create(['user_id' => $customer->id]);

        $conversation->messages()->create(['sender_id' => $customer->id, 'body' => 'Halo, butuh bantuan']);

        $this->assertSame(1, $conversation->unreadCountFor('staff'));
        $this->assertSame(1, Conversation::unreadCountForStaff());

        $conversation->markReadBy('staff');

        $this->assertSame(0, $conversation->fresh()->unreadCountFor('staff'));
        $this->assertSame(0, Conversation::unreadCountForStaff());
    }
}
