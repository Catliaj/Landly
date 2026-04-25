<?php

namespace App\Controllers\Messages;

use App\Controllers\BaseController;
use App\Models\LandListings;
use App\Models\Messages;
use App\Models\MessageSessions;
use App\Models\NotificationModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class MessageController extends BaseController
{
	public function getMessages(int $sessionId): ResponseInterface
	{
		$userId = $this->getCurrentUserId();
		if ($userId <= 0) {
			return $this->response->setStatusCode(401)->setJSON([
				'status' => 'error',
				'message' => 'Unauthorized.',
			]);
		}

		$sessionModel = new MessageSessions();
		$messagesModel = new Messages();

		$session = $this->findAccessibleSession($sessionModel, $sessionId, $userId);
		if ($session === null) {
			return $this->response->setStatusCode(404)->setJSON([
				'status' => 'error',
				'message' => 'Unauthorized or invalid session.',
			]);
		}

		$messages = $messagesModel
			->where('session_id', $sessionId)
			->orderBy('sent_at', 'ASC')
			->findAll();

		$unreadMessages = $messagesModel
			->where('session_id', $sessionId)
			->where('sender_id !=', $userId)
			->where('is_read', 0)
			->findAll();

		$messagesModel
			->where('session_id', $sessionId)
			->where('sender_id !=', $userId)
			->where('is_read', 0)
			->set(['is_read' => 1])
			->update();

		if ($unreadMessages !== []) {
			$groupedBySender = [];
			foreach ($unreadMessages as $message) {
				$senderId = (int) ($message['sender_id'] ?? 0);
				if ($senderId <= 0) {
					continue;
				}

				if (! isset($groupedBySender[$senderId])) {
					$groupedBySender[$senderId] = [
						'count' => 0,
						'latest_message_id' => 0,
					];
				}

				$groupedBySender[$senderId]['count']++;
				$groupedBySender[$senderId]['latest_message_id'] = max(
					$groupedBySender[$senderId]['latest_message_id'],
					(int) ($message['message_id'] ?? 0)
				);
			}

			foreach ($groupedBySender as $senderId => $summary) {
				$this->createNotificationSafely([
					'user_id' => $senderId,
					'notification_type' => 'message_read_state_changed',
					'notification_status' => 'active',
					'listing_id' => (int) ($session['listing_id'] ?? 0) ?: null,
					'inquiry_id' => (int) ($session['inquiry_id'] ?? 0) ?: null,
					'message_id' => (int) ($summary['latest_message_id'] ?? 0) ?: null,
					'message' => (($summary['count'] ?? 1) > 1)
						? 'Your recent messages were read.'
						: 'Your message was read.',
					'is_read' => 0,
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s'),
				]);
			}
		}

		return $this->response->setJSON([
			'status' => 'success',
			'session' => $session,
			'messages' => $messages,
		]);
	}

	public function sendMessage(): ResponseInterface
	{
		$userId = $this->getCurrentUserId();
		if ($userId <= 0) {
			return $this->response->setStatusCode(401)->setJSON([
				'status' => 'error',
				'message' => 'Unauthorized.',
			]);
		}

		$input = $this->request->getJSON(true);
		$sessionId = (int) ($input['session_id'] ?? $this->request->getPost('session_id') ?? 0);
		$messageText = trim((string) ($input['message_text'] ?? $input['messageText'] ?? $this->request->getPost('message_text') ?? $this->request->getPost('messageText') ?? ''));
		$attachmentPath = trim((string) ($input['attachment_path'] ?? $this->request->getPost('attachment_path') ?? ''));

		if ($sessionId <= 0 || ($messageText === '' && $attachmentPath === '')) {
			return $this->response->setStatusCode(422)->setJSON([
				'status' => 'error',
				'message' => 'session_id and message_text or attachment_path are required.',
			]);
		}

		$sessionModel = new MessageSessions();
		$messagesModel = new Messages();

		$session = $this->findAccessibleSession($sessionModel, $sessionId, $userId);
		if ($session === null) {
			return $this->response->setStatusCode(404)->setJSON([
				'status' => 'error',
				'message' => 'Unauthorized or invalid session.',
			]);
		}

		$now = date('Y-m-d H:i:s');
		$messageId = $messagesModel->insert([
			'session_id' => $sessionId,
			'sender_id' => $userId,
			'message_text' => $messageText !== '' ? $messageText : null,
			'attachment_path' => $attachmentPath !== '' ? $attachmentPath : null,
			'is_auto_reply' => 0,
			'is_read' => 0,
			'sent_at' => $now,
		]);

		$sessionModel->update($sessionId, ['last_message_at' => $now]);

		$recipientId = (int) (($userId === (int) ($session['buyer_id'] ?? 0))
			? ($session['seller_id'] ?? 0)
			: ($session['buyer_id'] ?? 0));

		if ($recipientId > 0) {
			$this->createNotificationSafely([
				'user_id' => $recipientId,
				'notification_type' => 'message_received',
				'notification_status' => 'active',
				'listing_id' => (int) ($session['listing_id'] ?? 0) ?: null,
				'inquiry_id' => (int) ($session['inquiry_id'] ?? 0) ?: null,
				'message_id' => (int) $messageId,
				'message' => 'You received a new message.',
				'is_read' => 0,
				'created_at' => $now,
				'updated_at' => $now,
			]);
		}

		return $this->response->setStatusCode(201)->setJSON([
			'status' => 'success',
			'message_id' => $messageId,
		]);
	}

	public function startSession(): ResponseInterface
	{
		$buyerId = $this->getCurrentUserId();
		if ($buyerId <= 0) {
			return $this->response->setStatusCode(401)->setJSON([
				'status' => 'error',
				'message' => 'User not logged in.',
			]);
		}

		$input = $this->request->getJSON(true) ?? [];
		$listingId = (int) ($input['listing_id'] ?? $input['PropertyID'] ?? $this->request->getPost('listing_id') ?? 0);
		$inquiryId = (int) ($input['inquiry_id'] ?? $this->request->getPost('inquiry_id') ?? 0);

		if ($listingId <= 0) {
			return $this->response->setStatusCode(422)->setJSON([
				'status' => 'error',
				'message' => 'listing_id is required.',
			]);
		}

		$listingModel = new LandListings();
		$sessionModel = new MessageSessions();
		$messagesModel = new Messages();
		$userModel = new UserModel();

		$listing = $listingModel->find($listingId);
		if ($listing === null) {
			return $this->response->setStatusCode(404)->setJSON([
				'status' => 'error',
				'message' => 'Listing not found.',
			]);
		}

		$sellerId = (int) ($listing['seller_id'] ?? 0);
		if ($sellerId <= 0) {
			return $this->response->setStatusCode(422)->setJSON([
				'status' => 'error',
				'message' => 'Listing has no seller assigned.',
			]);
		}

		if ($sellerId === $buyerId) {
			return $this->response->setStatusCode(422)->setJSON([
				'status' => 'error',
				'message' => 'Cannot start a chat with your own listing.',
			]);
		}

		$existingSession = $sessionModel
			->where('listing_id', $listingId)
			->where('buyer_id', $buyerId)
			->first();

		$now = date('Y-m-d H:i:s');

		if ($existingSession !== null) {
			$sessionId = (int) $existingSession['session_id'];
		} else {
			if ($inquiryId <= 0) {
				$inquiry = db_connect()
					->table('inquiries')
					->where('listing_id', $listingId)
					->where('buyer_id', $buyerId)
					->where('seller_id', $sellerId)
					->orderBy('inquiry_id', 'DESC')
					->get()
					->getRowArray();

				$inquiryId = (int) ($inquiry['inquiry_id'] ?? 0);
			}

			if ($inquiryId <= 0) {
				return $this->response->setStatusCode(422)->setJSON([
					'status' => 'error',
					'message' => 'inquiry_id is required before creating a message session.',
				]);
			}

			$sessionId = (int) $sessionModel->insert([
				'listing_id' => $listingId,
				'inquiry_id' => $inquiryId,
				'buyer_id' => $buyerId,
				'seller_id' => $sellerId,
				'session_status' => 'active',
				'last_message_at' => $now,
				'started_at' => $now,
			]);

			$initialText = 'Hi! I am interested in your property: ' . ($listing['title'] ?? 'this listing');
			$messagesModel->insert([
				'session_id' => $sessionId,
				'sender_id' => $buyerId,
				'message_text' => $initialText,
				'is_auto_reply' => 0,
				'is_read' => 0,
				'sent_at' => $now,
			]);
		}

		$seller = $userModel->find($sellerId);
		$sellerName = trim(($seller['first_name'] ?? '') . ' ' . ($seller['last_name'] ?? ''));

		return $this->response->setJSON([
			'status' => 'success',
			'session_id' => $sessionId,
			'seller_name' => $sellerName !== '' ? $sellerName : 'Seller',
			'listing_title' => $listing['title'] ?? 'this listing',
		]);
	}

	public function getSessions(): ResponseInterface
	{
		$userId = $this->getCurrentUserId();
		if ($userId <= 0) {
			return $this->response->setStatusCode(401)->setJSON([
				'status' => 'error',
				'message' => 'Unauthorized.',
			]);
		}

		$sessions = db_connect()
			->table('message_sessions ms')
			->select('ms.session_id, ms.listing_id, ms.inquiry_id, ms.buyer_id, ms.seller_id, ms.session_status, ms.last_message_at, ms.started_at, l.title AS listing_title, i.inquiry_status')
			->select("CONCAT(COALESCE(buyer.first_name, ''), ' ', COALESCE(buyer.last_name, '')) AS buyer_name", false)
			->select("CONCAT(COALESCE(seller.first_name, ''), ' ', COALESCE(seller.last_name, '')) AS seller_name", false)
			->select('(SELECT COUNT(*) FROM messages m WHERE m.session_id = ms.session_id AND m.sender_id != ' . (int) $userId . ' AND m.is_read = 0) AS unread_count', false)
			->join('land_listings l', 'l.listing_id = ms.listing_id', 'left')
			->join('inquiries i', 'i.inquiry_id = ms.inquiry_id', 'left')
			->join('users buyer', 'buyer.user_id = ms.buyer_id', 'left')
			->join('users seller', 'seller.user_id = ms.seller_id', 'left')
			->groupStart()
			->where('ms.buyer_id', $userId)
			->orWhere('ms.seller_id', $userId)
			->groupEnd()
			->orderBy('ms.last_message_at', 'DESC')
			->get()
			->getResultArray();

		$sessions = array_map(static function (array $session): array {
			$session['buyer_name'] = trim((string) ($session['buyer_name'] ?? ''));
			$session['seller_name'] = trim((string) ($session['seller_name'] ?? ''));
			return $session;
		}, $sessions);

		return $this->response->setJSON([
			'status' => 'success',
			'sessions' => $sessions,
		]);
	}

	private function findAccessibleSession(MessageSessions $sessionModel, int $sessionId, int $userId): ?array
	{
		return $sessionModel
			->groupStart()
			->where('buyer_id', $userId)
			->orWhere('seller_id', $userId)
			->groupEnd()
			->where('session_id', $sessionId)
			->first();
	}

	private function createNotificationSafely(array $payload): void
	{
		$db = db_connect();
		if (! $db->tableExists('notifications')) {
			return;
		}

		try {
			$notificationModel = new NotificationModel();
			$notificationModel->insert($payload);
		} catch (\Throwable $e) {
			log_message('error', 'Notification insert failed: {message}', ['message' => $e->getMessage()]);
		}
	}
}
