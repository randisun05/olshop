<?php

namespace App\Http\Controllers\Customer;

use App\Enums\ComplaintStatus;
use App\Enums\ComplaintType;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ComplaintController extends Controller
{
    public function index(Request $request): Response
    {
        $complaints = $request->user()->complaints()
            ->with('order')
            ->latest()
            ->paginate(10)
            ->through(fn (Complaint $complaint) => [
                'id' => $complaint->id,
                'order_number' => $complaint->order->order_number,
                'type_label' => $complaint->type->label(),
                'reason' => $complaint->reason,
                'status_label' => $complaint->status->label(),
                'admin_note' => $complaint->admin_note,
                'created_at' => $complaint->created_at->toIso8601String(),
            ]);

        return Inertia::render('Customer/Complaints/Index', [
            'complaints' => $complaints,
        ]);
    }

    public function create(Request $request, Order $order): Response
    {
        $this->authorizeAndGuard($request, $order);

        return Inertia::render('Customer/Complaints/Create', [
            'order' => [
                'order_number' => $order->order_number,
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'variant_label' => $item->variant_label,
                ]),
            ],
        ]);
    }

    public function store(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeAndGuard($request, $order);

        $validated = $request->validate([
            'order_item_id' => ['nullable', Rule::exists('order_items', 'id')->where('order_id', $order->id)],
            'type' => ['required', Rule::enum(ComplaintType::class)],
            'reason' => ['required', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'max:4096'],
        ]);

        $imagePath = $request->hasFile('image') ? $request->file('image')->store('complaints', 'public') : null;

        $order->complaints()->create([
            'order_item_id' => $validated['order_item_id'] ?? null,
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'reason' => $validated['reason'],
            'image_path' => $imagePath,
            'status' => ComplaintStatus::Pending,
        ]);

        return redirect()->route('customer.complaints.index')->with('success', 'Pengajuan Anda telah diterima, tim kami akan segera meninjau.');
    }

    private function authorizeAndGuard(Request $request, Order $order): void
    {
        abort_unless($order->user_id === $request->user()->id, 403);
        abort_unless($order->status === OrderStatus::Completed, 422, 'Retur/komplain hanya bisa diajukan untuk pesanan yang sudah selesai.');
        abort_if($order->complaints()->whereIn('status', [ComplaintStatus::Pending->value, ComplaintStatus::Processing->value])->exists(), 422, 'Sudah ada pengajuan yang masih berjalan untuk pesanan ini.');

        $order->loadMissing('items');
    }
}
