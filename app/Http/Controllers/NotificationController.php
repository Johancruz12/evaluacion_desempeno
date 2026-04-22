<?php

namespace App\Http\Controllers;

use App\Models\EvaluationNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = EvaluationNotification::where('user_id', $request->user()->id)
            ->with('evaluation.employee.person')
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function unreadCount(Request $request)
    {
        $count = EvaluationNotification::where('user_id', $request->user()->id)
            ->unread()
            ->count();

        return response()->json(['count' => $count]);
    }

    public function markAsRead(Request $request, EvaluationNotification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }

        $notification->update(['read_at' => now()]);

        if ($notification->evaluation_id) {
            return redirect()->route('evaluations.show', $notification->evaluation_id);
        }

        return back();
    }

    public function markAllAsRead(Request $request)
    {
        EvaluationNotification::where('user_id', $request->user()->id)
            ->unread()
            ->update(['read_at' => now()]);

        return back()->with('success', 'Todas las notificaciones han sido marcadas como leídas.');
    }
}
