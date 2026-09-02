<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmProfile;
use App\Models\ProductImage;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ClientCrmController extends Controller
{
    public function show(User $user)
    {
        abort_if($user->is_admin, 404);
        $user->load(['subscription.planModel', 'crmProfile']);
        $user->loadCount(['products', 'pages', 'banners', 'categories', 'customers', 'orders']);

        $tickets = $user->supportTickets()->with(['messages.author:id,name,is_admin'])->latest('last_activity_at')->get();
        $notes = $user->crmNotes()->with('author:id,name')->latest('occurred_at')->limit(50)->get();
        $payments = $user->payments()->latest()->limit(30)->get();
        $paid = $user->payments()->paid();
        $problemPayments = $user->payments()->whereIn('status', ['failed', 'refunded'])->count();
        $subscription = $user->subscription;

        return Inertia::render('Admin/ClientShow', [
            'client' => [
                'id' => $user->id, 'name' => $user->name, 'surname' => $user->surname,
                'email' => $user->email, 'business_name' => $user->business_name, 'slug' => $user->slug,
                'phone' => $user->phone_primary, 'whatsapp' => $user->whatsapp,
                'address' => $user->address, 'city' => $user->city, 'state' => $user->state, 'zip' => $user->zip,
                'created_at' => $user->created_at, 'is_active' => $user->is_active,
                'usage' => [
                    'products' => $user->products_count,
                    'product_images' => ProductImage::whereHas('product', fn ($query) => $query->where('user_id', $user->id))->count(),
                    'pages' => $user->pages_count, 'banners' => $user->banners_count,
                    'categories' => $user->categories_count, 'customers' => $user->customers_count, 'orders' => $user->orders_count,
                ],
                'subscription' => $subscription ? [
                    'plan_name' => $subscription->custom_plan_name ?: $subscription->planModel?->name,
                    'status' => $subscription->status->value,
                    'price' => (float) $subscription->price,
                    'billing_period' => $subscription->billing_period->value,
                    'trial_ends_at' => $subscription->trial_ends_at?->toDateString(),
                    'next_billing_at' => $subscription->next_billing_at?->toDateString(),
                    'status_changed_at' => $subscription->status_changed_at?->toISOString(),
                    'days_in_status' => (int) ($subscription->status_changed_at?->diffInDays(now()) ?? 0),
                ] : null,
                'payment_profile' => [
                    'paid_total' => (float) (clone $paid)->sum('amount'),
                    'paid_count' => (clone $paid)->count(),
                    'problem_count' => $problemPayments,
                    'last_paid_at' => $user->payments()->paid()->latest('paid_at')->value('paid_at'),
                ],
                'crm' => $user->crmProfile ?: ['health' => 'good', 'score' => 70, 'tags' => [], 'summary' => null, 'last_contact_at' => null, 'next_follow_up_at' => null],
            ],
            'notes' => $notes,
            'tickets' => $tickets,
            'payments' => $payments,
        ]);
    }

    public function updateProfile(Request $request, User $user)
    {
        $data = $request->validate([
            'health' => ['required', Rule::in(['good', 'attention', 'risk'])],
            'score' => ['required', 'integer', 'between:0,100'],
            'tags' => ['nullable', 'array', 'max:20'],
            'tags.*' => ['string', 'max:40'],
            'summary' => ['nullable', 'string', 'max:5000'],
            'next_follow_up_at' => ['nullable', 'date'],
        ]);
        CrmProfile::updateOrCreate(['user_id' => $user->id], $data);

        return back()->with('success', 'Perfil do cliente atualizado.');
    }

    public function addNote(Request $request, User $user)
    {
        $data = $request->validate(['type' => ['required', Rule::in(['note', 'call', 'whatsapp', 'email', 'meeting', 'payment', 'support'])], 'content' => ['required', 'string', 'max:10000']]);
        $user->crmNotes()->create($data + ['author_id' => $request->user()->id, 'occurred_at' => now()]);
        $user->crmProfile()->updateOrCreate(['user_id' => $user->id], ['last_contact_at' => now()]);

        return back()->with('success', 'Interação registrada no histórico.');
    }

    public function createTicket(Request $request, User $user)
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:180'], 'message' => ['required', 'string', 'max:10000'],
            'category' => ['required', Rule::in(['question', 'suggestion', 'technical', 'billing', 'other'])],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
        ]);
        $ticket = $user->supportTickets()->create([
            'number' => 'TKT-'.now()->format('ymd').'-'.Str::upper(Str::random(5)),
            'assigned_to' => $request->user()->id, 'subject' => $data['subject'], 'category' => $data['category'],
            'priority' => $data['priority'], 'status' => 'open', 'last_activity_at' => now(),
        ]);
        $ticket->messages()->create(['author_id' => $request->user()->id, 'message' => $data['message']]);

        return back()->with('success', 'Ticket criado com sucesso.');
    }

    public function updateTicket(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_progress', 'waiting_customer', 'resolved', 'closed'])],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'message' => ['nullable', 'string', 'max:10000'], 'is_internal' => ['boolean'],
        ]);
        $ticket->update(['status' => $data['status'], 'priority' => $data['priority'], 'assigned_to' => $request->user()->id, 'last_activity_at' => now(), 'resolved_at' => in_array($data['status'], ['resolved', 'closed'], true) ? now() : null]);
        if (! empty($data['message'])) {
            $ticket->messages()->create(['author_id' => $request->user()->id, 'message' => $data['message'], 'is_internal' => $data['is_internal'] ?? false]);
        }

        return back()->with('success', 'Ticket atualizado.');
    }
}
