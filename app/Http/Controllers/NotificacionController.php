<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificacionController extends Controller
{
    public function index()
    {
        $notificaciones = auth()->user()->notifications()->latest()->take(50)->get();
        $noLeidas = auth()->user()->unreadNotifications()->count();

        return response()->json([
            'notificaciones' => $notificaciones,
            'noLeidas' => $noLeidas,
        ]);
    }

    public function marcarLeida($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function marcarTodasLeidas()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    public function destroyAll()
    {
        auth()->user()->notifications()->delete();

        return response()->json(['success' => true]);
    }
}
