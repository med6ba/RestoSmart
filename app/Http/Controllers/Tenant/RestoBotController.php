<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\RestoBotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RestoBotController extends Controller
{
    public function index(Request $request): View
    {
        return view('tenant.admin.restobot', [
            'messages' => $request->session()->get('restobot.messages', []),
        ]);
    }

    public function store(Request $request, RestoBotService $bot): RedirectResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:1000'],
        ]);

        $messages = $request->session()->get('restobot.messages', []);
        $messages[] = ['role' => 'user', 'content' => $data['question']];
        $messages[] = ['role' => 'assistant', 'content' => $bot->answer($data['question'])];

        $request->session()->put('restobot.messages', array_slice($messages, -12));

        return back();
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->session()->forget('restobot.messages');

        return back();
    }
}
