<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $all = \Illuminate\Support\Facades\Cache::remember("notifications_{$user->id}", 60, function () use ($user) {
            // 1. Get recent transactions
            // Optimization: Use orderBy 'date' to utilize the new index [user_id, date]
            $transactions = \App\Models\Transaction::where('user_id', $user->id)
                ->orderBy('date', 'desc')
                ->take(10)
                ->get();
    
            $notifications = $transactions->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'title' => $transaction->type === 'expense' ? 'Nuevo Gasto' : 'Nuevo Ingreso',
                    'message' => "Registraste {$transaction->amount} en " . ($transaction->category ?? 'General'),
                    'date' => $transaction->created_at->diffForHumans(),
                    'type' => $transaction->type === 'expense' ? 'warning' : 'success'
                ];
            });
    
            // 2. Add System notifications (Mocked/Static for now)
            $systemNotifications = collect([
                [
                    'id' => 'sys-1',
                    'title' => 'Bienvenido',
                    'message' => "Hola {$user->name}, bienvenido a tu gestor financiero.",
                    'date' => $user->created_at->diffForHumans(),
                    'type' => 'info'
                ]
            ]);
    
            // Merge and sort
            return $notifications->merge($systemNotifications)->sortByDesc('date')->values();
        });

        return response()->json($all);
    }
}
