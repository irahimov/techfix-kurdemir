<?php
// routes/web.php

use App\Http\Controllers\TicketController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// ─── Public ───────────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('dashboard'));

// ─── Auth (Breeze) ────────────────────────────────────────────────────────────
require __DIR__ . '/auth.php';

// ─── Authenticated Routes ─────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard: rola görə yönləndir
    Route::get('/dashboard', function () {
        $user = auth()->user();
        if ($user->isAdmin() || $user->isAgent()) {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('tickets.index');
    })->name('dashboard');

    // ─── Müştəri Paneli ───────────────────────────────────────────────────────
    Route::get('/customer/panel', [TicketController::class, 'customerPanel'])->name('customer.panel');

    // ─── Biletlər (Bütün rollar) ───────────────────────────────────────────────
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', function () {
            if (auth()->user()->isCustomer()) {
                return redirect()->route('customer.panel');
            }
            return app(TicketController::class)->index(request());
        })->name('index');
        Route::get('/create',               [TicketController::class, 'create'])->name('create');
        Route::post('/',                    [TicketController::class, 'store'])->name('store');
        Route::get('/{ticket}',             [TicketController::class, 'show'])->name('show');
        
        // Blade-in istədiyi kimi 'messages.store' olaraq düzəldildi!
        Route::post('/{ticket}/message',    [TicketController::class, 'sendMessage'])->name('messages.store');
        
        Route::patch('/{ticket}/status',    [TicketController::class, 'updateStatus'])->name('status');
        Route::post('/{ticket}/assign',     [TicketController::class, 'assignAgent'])->name('assign');
        Route::post('/{ticket}/rate',       [TicketController::class, 'rate'])->name('rate');
    });

    // ─── Admin Panel ──────────────────────────────────────────────────────────
    Route::prefix('admin')->name('admin.')->middleware('admin.access')->group(function () {
        Route::get('/dashboard',         [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/reports',           [AdminController::class, 'reports'])->name('reports');
        Route::get('/users',             [AdminController::class, 'users'])->name('users');

        // Kateqoriyalar
        Route::get('/categories',        [AdminController::class, 'categories'])->name('categories');
        Route::post('/categories',       [AdminController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}',    [AdminController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');
    });

    // ─── API Endpoints (AJAX) ─────────────────────────────────────────────────
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/tickets/{ticket}/messages', function (App\Models\Ticket $ticket) {
            $user = auth()->user();
            
            $messages = $ticket->messages()
                ->with('user', 'attachments')
                ->where('created_at', '>', request('after', '2000-01-01'))
                ->get()
                ->map(fn($m) => [
                    'id'          => $m->id,
                    'message'     => $m->message,
                    'sender_type' => $m->sender_type,
                    'is_system'   => $m->is_system_message,
                    'user_name'   => $m->user->name,
                    'avatar_url'  => $m->user->avatar_url,
                    'created_at'  => $m->created_at->format('H:i'),
                    'date'        => $m->created_at->format('d.m.Y'),
                    'attachments' => $m->attachments->map(fn($a) => [
                        'url'  => $a->url,
                        'name' => $a->original_name,
                        'type' => $a->mime_type,
                    ]),
                ]);

            return response()->json($messages);
        })->name('ticket.messages');
    });
});