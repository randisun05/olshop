<?php

namespace Tests\Feature\Admin;

use App\Models\Conversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_view_conversation_list(): void
    {
        $admin = $this->createAdminUser();
        Conversation::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.chat.index'));

        $response->assertOk();
    }

    public function test_staff_can_reply_and_gets_assigned_to_conversation(): void
    {
        $admin = $this->createAdminUser();
        $conversation = Conversation::factory()->create();

        $response = $this->actingAs($admin)->postJson(route('admin.chat.message', $conversation), [
            'body' => 'Halo, ada yang bisa kami bantu?',
        ]);

        $response->assertOk();
        $response->assertJsonPath('message.is_mine', true);
        $this->assertSame($admin->id, $conversation->fresh()->assigned_to);
    }

    public function test_staff_can_close_and_reopen_a_conversation(): void
    {
        $admin = $this->createAdminUser();
        $conversation = Conversation::factory()->create();

        $this->actingAs($admin)->post(route('admin.chat.close', $conversation))->assertRedirect();
        $this->assertSame('closed', $conversation->fresh()->status->value);

        $this->actingAs($admin)->post(route('admin.chat.reopen', $conversation))->assertRedirect();
        $this->assertSame('open', $conversation->fresh()->status->value);
    }

    public function test_customer_cannot_manage_chat(): void
    {
        $customer = $this->createCustomerUser();

        $response = $this->actingAs($customer)->get(route('admin.chat.index'));

        $response->assertForbidden();
    }

    public function test_staff_without_chat_permission_is_forbidden(): void
    {
        $staffGudang = $this->createAdminUserWithoutTwoFactor();
        $staffGudang->syncRoles(['Staff Gudang']);
        $staffGudang->forceFill(['two_factor_confirmed_at' => now()])->save();

        $response = $this->actingAs($staffGudang)->get(route('admin.chat.index'));

        $response->assertForbidden();
    }
}
