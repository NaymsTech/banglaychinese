<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');

        $messages = ContactMessage::query()
            ->when($status === 'unread', function ($q) {
                $q->where('is_read', false);
            })
            ->when($status === 'read', function ($q) {
                $q->where('is_read', true);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.contact-messages.index', [
            'messages' => $messages,
            'currentStatus' => $status,
            'unreadCount' => ContactMessage::unread()->count(),
            'totalCount' => ContactMessage::count(),
        ]);
    }

    public function show(ContactMessage $message): View
    {
        if (! $message->is_read) {
            $message->update(['is_read' => true]);
        }

        return view('admin.contact-messages.show', [
            'message' => $message,
        ]);
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return redirect()->route('admin.contact-messages.index')->with('success', 'Message deleted successfully.');
    }
}
