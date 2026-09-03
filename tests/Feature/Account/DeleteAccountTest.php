<?php

namespace Tests\Feature\Account;

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_delete_own_account_with_correct_password(): void
    {
        $customer = $this->createCustomerUser();
        Address::factory()->create(['user_id' => $customer->id]);
        Wishlist::create(['user_id' => $customer->id, 'product_id' => Product::factory()->create()->id]);

        $response = $this->actingAs($customer)->delete(route('account.security.destroy-account'), [
            'password' => 'password',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $customer->id]);
        $this->assertDatabaseMissing('addresses', ['user_id' => $customer->id]);
        $this->assertDatabaseMissing('wishlists', ['user_id' => $customer->id]);
    }

    public function test_account_deletion_fails_with_wrong_password(): void
    {
        $customer = $this->createCustomerUser();

        $response = $this->actingAs($customer)->delete(route('account.security.destroy-account'), [
            'password' => 'salah-password',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseHas('users', ['id' => $customer->id]);
    }

    public function test_staff_cannot_delete_their_own_account(): void
    {
        $admin = $this->createAdminUserWithoutTwoFactor();

        $response = $this->actingAs($admin)->delete(route('account.security.destroy-account'), [
            'password' => 'password',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_orders_are_preserved_but_unlinked_when_account_is_deleted(): void
    {
        $customer = $this->createCustomerUser();
        $order = Order::factory()->create(['user_id' => $customer->id, 'order_number' => 'INV-TEST-DELETE']);

        $this->actingAs($customer)->delete(route('account.security.destroy-account'), [
            'password' => 'password',
        ]);

        $this->assertDatabaseHas('orders', ['order_number' => 'INV-TEST-DELETE', 'user_id' => null]);
    }
}
