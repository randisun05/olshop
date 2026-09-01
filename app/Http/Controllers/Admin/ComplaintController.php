<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ComplaintStatus;
use App\Http\Controllers\Controller;
use App\Models\Complaint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ComplaintController extends Controller
{
    public function index(Request $request): Response
    {
        $complaints = Complaint::query()
            ->with(['order', 'user'])
            ->when($request->string('status')->toString(), fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Complaint $complaint) => [
                'id' => $complaint->id,
                'order_number' => $complaint->order->order_number,
                'customer' => $complaint->user->name,
                'type_label' => $complaint->type->label(),
                'status' => $complaint->status->value,
                'status_label' => $complaint->status->label(),
                'created_at' => $complaint->created_at->toIso8601String(),
            ]);

        return Inertia::render('Admin/Complaints/Index', [
            'complaints' => $complaints,
            'filters' => $request->only('status'),
        ]);
    }

    public function show(Complaint $complaint): Response
    {
        $complaint->load(['order.items', 'orderItem', 'user']);

        return Inertia::render('Admin/Complaints/Show', [
            'complaint' => [
                'id' => $complaint->id,
                'order_number' => $complaint->order->order_number,
                'customer' => $complaint->user->name,
                'customer_email' => $complaint->user->email,
                'type_label' => $complaint->type->label(),
                'reason' => $complaint->reason,
                'image_url' => $complaint->imageUrl(),
                'order_item' => $complaint->orderItem ? [
                    'product_name' => $complaint->orderItem->product_name,
                    'variant_label' => $complaint->orderItem->variant_label,
                ] : null,
                'status' => $complaint->status->value,
                'status_label' => $complaint->status->label(),
                'admin_note' => $complaint->admin_note,
                'created_at' => $complaint->created_at->toIso8601String(),
            ],
        ]);
    }

    public function respond(Request $request, Complaint $complaint): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(ComplaintStatus::class)->except(ComplaintStatus::Pending)],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $complaint->updateStatus(ComplaintStatus::from($validated['status']), $validated['admin_note'] ?? null);

        return back()->with('success', 'Status pengajuan berhasil diperbarui.');
    }
}
