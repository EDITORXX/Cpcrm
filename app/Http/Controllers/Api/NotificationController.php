<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class NotificationController extends Controller
{
    /**
     * Get unread notifications for current user
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get recent notifications (both read and unread for dropdown)
        $notifications = AppNotification::where('user_id', $user->id)
            ->recent(50)
            ->with('telecallerTask.lead')
            ->orderBy('created_at', 'desc')
            ->get();

        $unreadCount = AppNotification::where('user_id', $user->id)
            ->unread()
            ->count();

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, AppNotification $notification)
    {
        $user = $request->user();

        if ($notification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark notification as clicked and return task URL
     */
    public function markAsClicked(Request $request, AppNotification $notification)
    {
        $user = $request->user();

        if ($notification->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $notification->markAsClicked();

        // Use action_url if available, otherwise fallback based on notification type
        $url = $notification->action_url;
        
        if (!$url) {
            // For verification notifications, navigate to verification-pending page
            if ($notification->type === AppNotification::TYPE_NEW_VERIFICATION && $notification->action_type === AppNotification::ACTION_VERIFICATION) {
                $url = url('/telecaller/verification-pending');
            } elseif ($notification->telecaller_task_id) {
                // For task-related notifications, navigate to tasks page
                $url = url('/telecaller/tasks?status=pending&task_id=' . $notification->telecaller_task_id);
            } elseif ($notification->type === AppNotification::TYPE_NEW_VERIFICATION) {
                // Fallback for verification notifications without action_url
                $url = url('/telecaller/verification-pending');
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification clicked',
            'url' => $url,
            'task_id' => $notification->telecaller_task_id,
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        AppNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Get unread notifications only
     */
    public function getUnread(Request $request)
    {
        $user = $request->user();
        
        $notifications = AppNotification::where('user_id', $user->id)
            ->unread()
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $unreadCount = $notifications->count();

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }
}
