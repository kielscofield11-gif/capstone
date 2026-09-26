<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = $request->user()->notifications()->latest()->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

    public function read(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        $item->markAsRead();

        $url = $item->data['url'] ?? null;

        if (is_string($url) && $url !== '' && $this->isInternalUrl($url)) {
            return redirect()->to($url);
        }

        return redirect()->route('notifications.index');
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);
        return back()->with('success', 'All notifications marked as read.');
    }

    private function isInternalUrl(string $url): bool
    {
        // Allow only relative URLs or absolute URLs on this app's host.
        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return true;
        }

        $parts = parse_url($url);
        if ($parts === false || ! isset($parts['host'])) {
            return false;
        }

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);

        return $appHost !== null && strtolower($parts['host']) === strtolower($appHost);
    }
}
