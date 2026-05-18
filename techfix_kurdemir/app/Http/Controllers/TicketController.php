<?php
// app/Http/Controllers/TicketController.php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Category;
use App\Models\TicketMessage;
use App\Models\Attachment;
use App\Services\SlaService;
use App\Services\AutoAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function __construct(
        private readonly SlaService $slaService,
        private readonly AutoAssignmentService $assignmentService
    ) {}

    // ─── Müraciət siyahısı (Müştəri / Admin Görünüşü Ayrıldı) ─────────────────

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Ticket::with(['category', 'agent'])->latest();

        // Əgər istifadəçi təmiz müştəridirsə, yalnız öz müraciətlərini görsün
        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        } 
        elseif ($user->isAgent()) {
            $query->where('assigned_to', $user->id);
        }

        // Filtrlər
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%")
                  ->orWhere('device_model', 'like', "%{$search}%");
            });
        }

        $tickets = $query->paginate(15)->withQueryString();
        $stats = $this->getStatsForUser($user, $request);

        return view('tickets.index', compact('tickets', 'stats'));
    }

    // ─── Müştəri: Müraciət yaratma formu ──────────────────────────────────────

    public function create(Request $request)
    {
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();
        $is_customer_panel = $request->boolean('from_customer_panel') || session('from_customer_panel');
        return view('tickets.create', compact('categories', 'is_customer_panel'));
    }

    // ─── Müştəri: Müraciət yaratma (POST) ─────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'         => 'required|string|min:5|max:255',
            'description'   => 'required|string|min:20|max:5000',
            'category_id'   => 'required|exists:categories,id',
            'priority'      => 'required|in:urgent,high,medium,low',
            'device_model'  => 'nullable|string|max:100',
            'device_serial' => 'nullable|string|max:100',
            'attachments'   => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB
        ], [
            'title.required'       => 'Başlıq mütləqdir.',
            'title.min'            => 'Başlıq ən azı 5 simvol olmalıdır.',
            'description.required' => 'Açıxlama mütləqdir.',
            'description.min'      => 'Açıqlama ən azı 20 simvol olmalıdır.',
            'category_id.required' => 'Kateqoriya seçilməlidir.',
            'priority.required'    => 'Prioritet seçilməlidir.',
            'attachments.*.mimes'  => 'Yalnız JPG, PNG və PDF fayllar qəbul edilir.',
            'attachments.*.max'    => 'Hər fayl maksimum 5MB ola bilər.',
        ]);

        $ticket = Ticket::create([
            'ticket_number' => Ticket::generateTicketNumber(),
            'title'         => $validated['title'],
            'description'   => $validated['description'],
            'category_id'   => $validated['category_id'],
            'customer_id'   => Auth::id(),
            'priority'      => $validated['priority'],
            'device_model'  => $validated['device_model'] ?? null,
            'device_serial' => $validated['device_serial'] ?? null,
            'status'        => 'new',
            'sla_deadline'  => Ticket::calculateSlaDeadline($validated['priority']),
        ]);

        if ($request->hasFile('attachments')) {
            $this->uploadAttachments($request->file('attachments'), $ticket);
        }

        if (trim($validated['description']) !== '') {
            TicketMessage::create([
                'ticket_id'   => $ticket->id,
                'user_id'     => Auth::id(),
                'message'     => $validated['description'],
                'sender_type' => 'customer',
            ]);
        }

        $this->assignmentService->assign($ticket);

        $redirectRoute = $request->has('from_customer_panel')
            ? route('customer.panel')
            : route('tickets.show', $ticket->id);

        return redirect($redirectRoute)
            ->with('success', "Müraciətiniz uğurla yaradıldı! Nömrə: {$ticket->ticket_number}");
    }

    // ─── Müraciət detalları ───────────────────────────────────────────────────

    public function show($id)
    {
        $ticket = Ticket::findOrFail($id);

        $this->authorizeView($ticket);

        $ticket->load(['category', 'customer', 'agent', 'messages.user', 'messages.attachments', 'attachments']);

        $ticket->messages()
            ->where('user_id', '!=', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        $agents = [];
        if (Auth::user()->isAdmin()) {
            $agents = \App\Models\User::role('support_agent')->where('is_active', true)->get();
        }

        return view('tickets.show', compact('ticket', 'agents'));
    }

    // ─── Mesaj göndər ─────────────────────────────────────────────────────────

    public function sendMessage(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $this->authorizeView($ticket);

        $request->validate([
            'message'       => 'required|string|min:1|max:5000',
            'attachments'   => 'nullable|array|max:3',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'message.required' => 'Mesaj boş ola bilməz.',
        ]);

        $user       = Auth::user();
        $senderType = $user->isCustomer() ? 'customer' : 'agent';

        $message = TicketMessage::create([
            'ticket_id'   => $ticket->id,
            'user_id'     => $user->id,
            'message'     => $request->message,
            'sender_type' => $senderType,
        ]);

        if ($request->hasFile('attachments')) {
            $this->uploadAttachments($request->file('attachments'), $ticket, $message->id);
        }

        if ($user->isAgent()) {
            $this->slaService->resetOnAgentResponse($ticket);

            if ($ticket->status === 'new' || $ticket->status === 'waiting_agent') {
                $ticket->update([
                    'status'            => 'waiting_customer',
                    'first_response_at' => $ticket->first_response_at ?? now(),
                ]);
            }
        } elseif ($user->isCustomer()) {
            if ($ticket->status === 'waiting_customer') {
                $ticket->update(['status' => 'waiting_agent']);
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id'          => $message->id,
                    'message'     => $message->message,
                    'sender_type' => $message->sender_type,
                    'user_name'   => $user->name,
                    'avatar_url'  => $user->avatar_url,
                    'created_at'  => $message->created_at->format('H:i'),
                ],
            ]);
        }

        return back()->with('success', 'Mesajınız göndərildi.');
    }

    // ─── Status dəyişdir (Ekspert/Admin) ──────────────────────────────────────

    public function updateStatus(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'status'          => 'required|in:new,in_progress,waiting_agent,waiting_customer,in_service,resolved,closed',
            'tracking_number' => 'nullable|string|max:100',
        ]);

        if (!Auth::user()->isAdmin() && !Auth::user()->isAgent()) {
            abort(403, 'Bu əməliyyat üçün icazəniz yoxdur.');
        }

        $oldStatus = $ticket->status;
        $newStatus = $request->status;

        $updateData = ['status' => $newStatus];

        if ($newStatus === 'resolved') {
            $updateData['resolved_at'] = now();
        } elseif ($newStatus === 'closed') {
            $updateData['closed_at'] = now();
        }

        if ($request->filled('tracking_number')) {
            $updateData['tracking_number'] = $request->tracking_number;
        }

        $ticket->update($updateData);

        $statusLabels = [
            'new' => 'Yeni Müraciət', 
            'in_progress' => 'Baxılır',
            'waiting_agent' => 'Ekspert Cavab Gözləyir',
            'waiting_customer' => 'Müştəri Cavab Gözləyir',
            'in_service' => 'Kuryerdə/Servisdə',
            'resolved' => 'Həll Olundu', 
            'closed' => 'Bağlandı',
        ];

        TicketMessage::create([
            'ticket_id'         => $ticket->id,
            'user_id'           => Auth::id(),
            'message'           => "🔄 Status dəyişdirildi: **{$statusLabels[$oldStatus]}** → **{$statusLabels[$newStatus]}**",
            'sender_type'      => 'system',
            'is_system_message' => true,
            'system_event'     => 'status_changed',
        ]);

        return back()->with('success', 'Status uğurla yeniləndi.');
    }

    // ─── Ekspert Təyini (Admin) ───────────────────────────────────────────────

    public function assignAgent(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate(['agent_id' => 'required|exists:users,id']);

        $agent = \App\Models\User::findOrFail($request->agent_id);
        $this->assignmentService->manualAssign($ticket, $agent, Auth::user());

        return back()->with('success', "Müraciət {$agent->name} mütəxəssisinə təyin edildi.");
    }

    // ─── Qiymətləndirmə (Müştəri) ─────────────────────────────────────────────

    public function rate(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        if (Auth::id() !== $ticket->customer_id) {
            abort(403);
        }

        $request->validate([
            'rating'         => 'required|integer|min:1|max:5',
            'rating_comment' => 'nullable|string|max:500',
        ]);

        $ticket->update([
            'rating'         => $request->rating,
            'rating_comment' => $request->rating_comment,
            'status'         => 'closed',
            'closed_at'      => now(),
        ]);

        return back()->with('success', 'Qiymətləndirməniz üçün təşəkkür edirik!');
    }

    // ─── Müştəri Paneli (Admin görünüşü) ─────────────────────────────────────

    public function customerPanel(Request $request)
    {
        $user = Auth::user();

        $query = Ticket::with(['category', 'agent', 'customer'])->latest();

        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('ticket_number', 'like', "%{$search}%")
                  ->orWhere('device_model', 'like', "%{$search}%");
            });
        }

        $tickets = $query->paginate(15)->withQueryString();
        $stats   = $this->getStatsForUser($user);

        return view('tickets.index', compact('tickets', 'stats'))
            ->with('is_customer_panel', true);
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    private function uploadAttachments(array $files, Ticket $ticket, ?int $messageId = null): void
    {
        foreach ($files as $file) {
            $storedName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path       = $file->storeAs('tickets/' . $ticket->id, $storedName, 'public');

            Attachment::create([
                'ticket_id'         => $ticket->id,
                'ticket_message_id' => $messageId,
                'uploaded_by'       => Auth::id(),
                'original_name'     => $file->getClientOriginalName(),
                'stored_name'       => $storedName,
                'file_path'         => $path,
                'file_type'         => $file->getClientOriginalExtension(),
                'mime_type'         => $file->getMimeType(),
                'file_size'         => $file->getSize(),
            ]);
        }
    }

    private function authorizeView(Ticket $ticket): void
    {
        $user = Auth::user();

        if ($user->isAdmin() && request()->has('view_as_customer')) {
            return;
        }

        if ($user->isCustomer() && $ticket->customer_id !== $user->id) {
            abort(403, 'Bu müraciətə baxmaq üçün icazəniz yoxdur.');
        }

        if ($user->isAgent() && $ticket->assigned_to !== $user->id) {
            abort(403, 'Bu müraciət sizə təyin edilməyib.');
        }
    }

    private function getStatsForUser($user, $request = null): array
    {
        $query = Ticket::query();

        if ($user->isCustomer() || ($user->isAdmin() && $request && $request->has('view_as_customer'))) {
            $query->where('customer_id', $user->id);
        } elseif ($user->isAgent()) {
            $query->where('assigned_to', $user->id);
        }

        return [
            'total'    => (clone $query)->count(),
            'open'     => (clone $query)->active()->count(),
            'resolved' => (clone $query)->where('status', 'resolved')->count(),
            'violated' => (clone $query)->where('is_sla_violated', true)->count(),
        ];
    }
}