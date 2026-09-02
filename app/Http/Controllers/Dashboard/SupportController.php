<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SupportController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Support/Index', ['tickets' => $request->user()->supportTickets()->with(['messages' => fn ($query) => $query->where('is_internal', false)->with('author:id,name,is_admin')])->latest('last_activity_at')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['subject' => ['required', 'string', 'max:180'], 'message' => ['required', 'string', 'max:10000'], 'category' => ['required', Rule::in(['question', 'suggestion', 'technical', 'billing', 'other'])]]);
        $ticket = $request->user()->supportTickets()->create(['number' => 'TKT-'.now()->format('ymd').'-'.Str::upper(Str::random(5)), 'subject' => $data['subject'], 'category' => $data['category'], 'priority' => 'normal', 'status' => 'open', 'last_activity_at' => now()]);
        $ticket->messages()->create(['author_id' => $request->user()->id, 'message' => $data['message']]);

        return back()->with('success', 'Chamado aberto com sucesso.');
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        abort_unless($ticket->user_id === $request->user()->id, 403);
        abort_if($ticket->status === 'closed', 422, 'Este chamado está encerrado.');
        $data = $request->validate(['message' => ['required', 'string', 'max:10000']]);
        $ticket->messages()->create(['author_id' => $request->user()->id, 'message' => $data['message']]);
        $ticket->update(['status' => 'open', 'last_activity_at' => now()]);

        return back()->with('success', 'Mensagem enviada.');
    }
}
