<?php

namespace App\Http\Controllers;

use App\Jobs\SendWebPushNotificationJob;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Http\Request;

class TestPwaPushController extends Controller
{
    /**
     * Show test page: select user and send a test PWA push notification.
     */
    public function index()
    {
        $users = User::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role_id'])
            ->load('role:id,name');

        $usersWithPush = PushSubscription::select('user_id')->distinct()->pluck('user_id')->flip();
        foreach ($users as $u) {
            $u->has_push_subscription = $usersWithPush->has($u->id);
        }

        return view('test.pwa-push', compact('users'));
    }

    /**
     * Send test PWA push to the selected user.
     */
    public function send(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $title = 'New Lead Assigned (Test)';
        $body = 'You have been assigned a new lead. (This is a test notification.)';
        $url = $user->isTelecaller() || $user->isSalesExecutive()
            ? route('telecaller.tasks') . '?status=pending'
            : route('leads.index');

        SendWebPushNotificationJob::dispatch($user->id, $title, $body, $url, 'test-pwa-push-' . time());

        return redirect()->route('test.pwa-push')
            ->with('success', "Test PWA notification queued for {$user->name}. If they have the app open (or PWA installed with notifications allowed), they should see it shortly.");
    }
}
