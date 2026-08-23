<?php

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class MergeGuestCartOnLogin
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly Request $request,
    ) {}

    public function handle(Login $event): void
    {
        $this->cartService->mergeGuestCartIntoUser($this->request->session()->getId(), $event->user);
    }
}
