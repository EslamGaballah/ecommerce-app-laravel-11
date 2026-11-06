<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ContactMessagesController extends Controller
{
    public function index(): View
    {
        return view('dashboard.contact_messages.index', [
            'messages' => ContactMessage::latest()->latest('id')->paginate(15),
        ]);
    }

    public function show(ContactMessage $message): View
    {
        if (! $message->read_at) {
            $message->forceFill(['read_at' => now()])->save();
        }

        return view('dashboard.contact_messages.show', compact('message'));
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return redirect()->route('dashboard.messages.index')->with('success', __('app.deleted_successfully'));
    }
}
