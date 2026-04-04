<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(20);
        return view('admin.notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        auth()->user()->notifications()->where('id', $id)->update(['read_at' => now()]);
        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    }
}
