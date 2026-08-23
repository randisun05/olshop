<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isGuest = ! $this->user();
        $usesSavedAddress = $this->boolean('use_saved_address');

        return [
            'use_saved_address' => ['boolean'],
            'address_id' => [
                Rule::requiredIf($usesSavedAddress && ! $isGuest),
                Rule::exists('addresses', 'id')->where('user_id', $this->user()?->id),
            ],

            'recipient_name' => [Rule::requiredIf(! $usesSavedAddress), 'nullable', 'string', 'max:255'],
            'phone' => [Rule::requiredIf(! $usesSavedAddress), 'nullable', 'string', 'max:30'],
            'city' => [Rule::requiredIf(! $usesSavedAddress), 'nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'address_line' => [Rule::requiredIf(! $usesSavedAddress), 'nullable', 'string'],

            'guest_name' => [Rule::requiredIf($isGuest), 'nullable', 'string', 'max:255'],
            'guest_email' => [Rule::requiredIf($isGuest), 'nullable', 'email', 'max:255'],
            'guest_phone' => [Rule::requiredIf($isGuest), 'nullable', 'string', 'max:30'],

            'shipping_zone_id' => ['required', Rule::exists('shipping_zones', 'id')->where('is_active', true)],
            'payment_method' => ['required', Rule::in(['midtrans', 'manual_transfer'])],
            'notes' => ['nullable', 'string', 'max:1000'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ];
    }
}
