<?php

namespace Tests\Feature\Admin;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use App\Notifications\ComplaintStatusUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ComplaintManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_complaint_list(): void
    {
        $admin = $this->createAdminUser();
        Complaint::factory()->create();

        $response = $this->actingAs($admin)->get(route('admin.complaints.index'));

        $response->assertOk();
    }

    public function test_admin_can_resolve_a_complaint_and_customer_is_notified(): void
    {
        Notification::fake();
        $admin = $this->createAdminUser();
        $complaint = Complaint::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.complaints.respond', $complaint), [
            'status' => 'resolved',
            'admin_note' => 'Dana refund sudah dikirim.',
        ]);

        $response->assertRedirect();
        $this->assertSame(ComplaintStatus::Resolved, $complaint->fresh()->status);
        $this->assertNotNull($complaint->fresh()->resolved_at);

        Notification::assertSentTo($complaint->user, ComplaintStatusUpdated::class);
    }

    public function test_customer_cannot_manage_complaints(): void
    {
        $customer = $this->createCustomerUser();

        $response = $this->actingAs($customer)->get(route('admin.complaints.index'));

        $response->assertForbidden();
    }

    public function test_status_cannot_be_set_back_to_pending(): void
    {
        $admin = $this->createAdminUser();
        $complaint = Complaint::factory()->create();

        $response = $this->actingAs($admin)->post(route('admin.complaints.respond', $complaint), [
            'status' => 'pending',
        ]);

        $response->assertSessionHasErrors('status');
    }
}
