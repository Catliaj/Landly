<?php

namespace App\Controllers\Notification;

use App\Controllers\BaseController;
use App\Models\NotificationModel;
use CodeIgniter\HTTP\ResponseInterface;

class NotificationController extends BaseController
{
    public function getNotifications(): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ]);
        }

        $notificationModel = new NotificationModel();
        $notifications = $notificationModel
            ->where('user_id', $userId)
            ->where('notification_status', 'active')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'notifications' => $notifications,
        ]);
    }

    public function getUnreadCount(): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ]);
        }

        $notificationModel = new NotificationModel();
        $count = $notificationModel
            ->where('user_id', $userId)
            ->where('notification_status', 'active')
            ->where('is_read', 0)
            ->countAllResults();

        return $this->response->setJSON([
            'status' => 'success',
            'unread_count' => $count,
        ]);
    }

    public function markAsRead($notificationId): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ]);
        }

        $notificationModel = new NotificationModel();
        $notification = $notificationModel->find((int) $notificationId);
        if ($notification === null || (int) ($notification['user_id'] ?? 0) !== $userId) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Notification not found.',
            ]);
        }

        $notificationModel->update((int) $notificationId, [
            'is_read' => true,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Notification marked as read.',
        ]);
    }

    public function markAllAsRead(): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ]);
        }

        $notificationModel = new NotificationModel();
        $notificationModel
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->set([
                'is_read' => 1,
                'updated_at' => date('Y-m-d H:i:s'),
            ])
            ->update();

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'All notifications marked as read.',
        ]);
    }

    public function archiveNotification($notificationId): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ]);
        }

        $notificationModel = new NotificationModel();
        $notification = $notificationModel->find((int) $notificationId);
        if ($notification === null || (int) ($notification['user_id'] ?? 0) !== $userId) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Notification not found.',
            ]);
        }

        $notificationModel->update((int) $notificationId, [
            'notification_status' => 'archived',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Notification archived.',
        ]);
    }
}
