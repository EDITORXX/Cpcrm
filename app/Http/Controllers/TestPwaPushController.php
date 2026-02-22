<?php

namespace App\Http\Controllers;

use App\Jobs\SendWebPushNotificationJob;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class TestPwaPushController extends Controller
{
    /**
     * One-click PWA push diagnostic for a user (e.g. Gold). Output copy-paste for support.
     */
    public function diagnose(Request $request)
    {
        $userId = $request->query('user_id');
        $user = null;
        if ($userId) {
            $user = User::find($userId);
        }
        if (! $user) {
            $user = User::where('is_active', true)
                ->where(function ($q) {
                    $q->where('name', 'like', '%Gold%')
                        ->orWhere('email', 'like', '%gold%');
                })
                ->first();
        }
        if (! $user) {
            $user = User::where('is_active', true)->orderBy('id')->first();
        }

        $report = [
            'generated_at' => now()->toIso8601String(),
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name ?? null,
            ] : null,
        ];

        if (! $user) {
            $report['error'] = 'No user found (add ?user_id=19 to URL for specific user).';
            return view('test.pwa-diagnose', ['report' => $report, 'reportText' => $this->reportToText($report)]);
        }

        $subscriptions = PushSubscription::where('user_id', $user->id)->get();
        $report['push_subscriptions'] = [
            'count' => $subscriptions->count(),
            'rows' => $subscriptions->map(fn ($s) => [
                'id' => $s->id,
                'endpoint_preview' => strlen($s->endpoint) > 60 ? substr($s->endpoint, 0, 60) . '...' : $s->endpoint,
                'has_keys' => ! empty($s->keys),
                'created_at' => $s->created_at?->toIso8601String(),
            ])->toArray(),
        ];

        $vapidPublic = config('webpush.vapid_public');
        $vapidPrivate = config('webpush.vapid_private');
        $report['vapid'] = [
            'public_set' => ! empty($vapidPublic),
            'public_preview' => $vapidPublic ? substr($vapidPublic, 0, 20) . '...' : '',
            'private_set' => ! empty($vapidPrivate),
        ];

        $report['webpush_package'] = class_exists(\Minishlink\WebPush\WebPush::class);

        $report['queue'] = [
            'connection' => config('queue.default'),
        ];

        $logPath = storage_path('logs/laravel.log');
        $report['log_tail'] = [];
        if (File::exists($logPath)) {
            $lines = array_slice(file($logPath) ?: [], -100);
            $relevant = array_filter($lines, fn ($line) => preg_match('/Web Push|push_subscription|SendWebPushNotificationJob|VAPID|WebPush/i', $line));
            $report['log_tail'] = array_values(array_slice($relevant, -25));
        }

        $reportText = $this->reportToText($report);
        return view('test.pwa-diagnose', ['report' => $report, 'reportText' => $reportText]);
    }

    private function reportToText(array $report): string
    {
        $out = "--- PWA Push Diagnostic ---\n";
        $out .= "Generated: " . ($report['generated_at'] ?? '') . "\n\n";
        if (! empty($report['error'])) {
            $out .= "Error: " . $report['error'] . "\n";
            return $out;
        }
        $out .= "User: " . json_encode($report['user'] ?? [], JSON_PRETTY_PRINT) . "\n\n";
        $out .= "Push subscriptions: " . json_encode($report['push_subscriptions'] ?? [], JSON_PRETTY_PRINT) . "\n\n";
        $out .= "VAPID: " . json_encode($report['vapid'] ?? [], JSON_PRETTY_PRINT) . "\n\n";
        $out .= "Web Push package installed: " . (($report['webpush_package'] ?? false) ? 'yes' : 'no') . "\n\n";
        $out .= "Queue: " . json_encode($report['queue'] ?? [], JSON_PRETTY_PRINT) . "\n\n";
        $out .= "Relevant log lines:\n" . implode('', $report['log_tail'] ?? []);
        return $out;
    }

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
