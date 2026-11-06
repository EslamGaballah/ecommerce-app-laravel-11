<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('front.pages.contact');
    }

    public function store(Request $request): RedirectResponse
    {
        // Real visitors never see the "website" field, so anything in it came from a bot.
        // The bot gets the normal answer and nothing is saved.
        if ($request->filled('website')) {
            return redirect()->route('contact')->with('success', __('app.contact_message_sent'));
        }

        ContactMessage::create($request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]));

        return redirect()->route('contact')->with('success', __('app.contact_message_sent'));
    }
}
