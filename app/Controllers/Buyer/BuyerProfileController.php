<?php

namespace App\Controllers\Buyer;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;

class BuyerProfileController extends BaseController
{
	public function index(): ResponseInterface
	{
		$buyerId = $this->getCurrentUserId();
		if ($buyerId <= 0) {
			return $this->response->setStatusCode(401)->setJSON([
				'status' => 'error',
				'message' => 'Unauthorized.',
			]);
		}

		return $this->response->setJSON([
			'status' => 'success',
			'profile' => $this->buildBuyerProfilePayload($buyerId),
		]);
	}

	public function show(int $buyerId = 0): ResponseInterface
	{
		$currentUserId = $this->getCurrentUserId();
		$buyerId = $buyerId > 0 ? $buyerId : $currentUserId;
		if ($currentUserId <= 0 || $buyerId !== $currentUserId) {
			return $this->respondWithError('Unauthorized.', 401);
		}

		if ($buyerId <= 0) {
			return $this->response->setStatusCode(401)->setJSON([
				'status' => 'error',
				'message' => 'Unauthorized.',
			]);
		}

		return $this->response->setJSON([
			'status' => 'success',
			'profile' => $this->buildBuyerProfilePayload($buyerId),
		]);
	}

	public function store(): ResponseInterface
	{
		return $this->saveBuyerProfile();
	}

	public function update(int $buyerId = 0): ResponseInterface
	{
		return $this->saveBuyerProfile($buyerId);
	}

	public function delete(int $buyerId = 0): ResponseInterface
	{
		$currentUserId = $this->getCurrentUserId();
		$buyerId = $buyerId > 0 ? $buyerId : $currentUserId;
		if ($currentUserId <= 0 || $buyerId !== $currentUserId) {
			return $this->respondWithError('Unauthorized.', 401);
		}

		$userModel = new UserModel();
		if (! $userModel->update($buyerId, ['profile_picture' => ''])) {
			$error = implode(' ', $userModel->errors() ?: ['Failed to clear profile picture.']);
			return $this->respondWithError($error, 500);
		}

		return $this->respondWithSuccess('Profile picture cleared.', $buyerId);
	}

	private function saveBuyerProfile(int $buyerId = 0): ResponseInterface
	{
		$currentUserId = $this->getCurrentUserId();
		$buyerId = $buyerId > 0 ? $buyerId : $currentUserId;
		if ($currentUserId <= 0 || $buyerId !== $currentUserId) {
			return $this->respondWithError('Unauthorized.', 401);
		}

		$rules = [
			'first_name' => 'required|max_length[255]',
			'last_name' => 'required|max_length[255]',
			'email' => 'required|valid_email|max_length[255]',
		];

		if (! $this->validate($rules)) {
			return $this->respondWithError('Please correct the profile form and try again.', 422, $this->validator->getErrors());
		}

		$userData = [
			'first_name' => trim((string) $this->request->getPost('first_name')),
			'last_name' => trim((string) $this->request->getPost('last_name')),
			'email' => trim((string) $this->request->getPost('email')),
		];

		$db = Database::connect();
		$db->transBegin();

		try {
			$userModel = new UserModel();
			if (! $userModel->update($buyerId, $userData)) {
				$error = implode(' ', $userModel->errors() ?: ['Failed to update buyer account.']);
				throw new \RuntimeException($error);
			}

			if ($db->transStatus() === false) {
				throw new \RuntimeException('Transaction failed while saving buyer profile.');
			}

			$db->transCommit();

			return $this->respondWithSuccess('Buyer profile updated successfully.', $buyerId);
		} catch (\Throwable $e) {
			$db->transRollback();

			return $this->respondWithError($e->getMessage(), 500);
		}
	}

	private function buildBuyerProfilePayload(int $buyerId): array
	{
		$userModel = new UserModel();

		$user = $userModel->find($buyerId) ?? [];

		$fullName = trim((string) (($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
		$fullName = $fullName !== '' ? $fullName : 'Buyer';
		$isActive = (int) ($user['is_active'] ?? 0) === 1;

		return [
			'full_name' => $fullName,
			'email' => trim((string) ($user['email'] ?? 'N/A')),
			'avatar_url' => $this->resolveUserProfilePictureUrl((string) ($user['profile_picture'] ?? '')),
			'initials' => $this->formatInitials($fullName),
			'first_name' => trim((string) ($user['first_name'] ?? '')),
			'last_name' => trim((string) ($user['last_name'] ?? '')),
			'status_label' => $isActive ? 'Buyer' : 'Inactive Buyer',
			'status_class' => $isActive ? 'active' : 'inactive',
			'stats' => $this->getBuyerProfileStats($buyerId),
		];
	}

	private function getBuyerProfileStats(int $buyerId): array
	{
		$db = Database::connect();

		$savedProperties = $db->table('buyer_favorites')
			->where('buyer_id', $buyerId)
			->countAllResults();

		$acceptedInquiries = $db->table('inquiries')
			->where('buyer_id', $buyerId)
			->where('inquiry_status', 'accepted')
			->countAllResults();

		$unreadMessages = $db->table('messages m')
			->select('COUNT(*) AS total_unread')
			->join('message_sessions ms', 'ms.session_id = m.session_id', 'inner')
			->groupStart()
			->where('ms.buyer_id', $buyerId)
			->orWhere('ms.seller_id', $buyerId)
			->groupEnd()
			->where('m.sender_id !=', $buyerId)
			->where('m.is_read', 0)
			->get()
			->getRowArray();

		return [
			'saved_properties' => (int) $savedProperties,
			'accepted_inquiries' => (int) $acceptedInquiries,
			'unread_messages' => (int) ($unreadMessages['total_unread'] ?? 0),
		];
	}

	private function resolveUserProfilePictureUrl(string $profilePicture): string
	{
		$profilePicture = trim($profilePicture);
		if ($profilePicture === '') {
			return '';
		}

		if (preg_match('#^(?:https?:)?//#i', $profilePicture) === 1 || str_starts_with($profilePicture, 'data:')) {
			return $profilePicture;
		}

		return base_url('media/profile?path=' . rawurlencode($profilePicture));
	}

	private function formatInitials(string $fullName): string
	{
		$parts = array_values(array_filter(array_map('trim', preg_split('/\s+/', trim($fullName)) ?: [])));
		if ($parts === []) {
			return 'NA';
		}

		$initials = '';
		foreach (array_slice($parts, 0, 2) as $part) {
			$initials .= strtoupper(substr($part, 0, 1));
		}

		return $initials !== '' ? $initials : 'NA';
	}

	private function respondWithSuccess(string $message, int $buyerId): ResponseInterface
	{
		if ($this->request->isAJAX()) {
			return $this->response->setJSON([
				'status' => 'success',
				'message' => $message,
				'profile' => $this->buildBuyerProfilePayload($buyerId),
			]);
		}

		return redirect()->to(base_url('buyer/dashboard'))->with('buyer_profile_message', $message);
	}

	private function respondWithError(string $message, int $statusCode = 400, array $errors = []): ResponseInterface
	{
		if ($this->request->isAJAX()) {
			$payload = [
				'status' => 'error',
				'message' => $message,
			];

			if ($errors !== []) {
				$payload['errors'] = $errors;
			}

			return $this->response->setStatusCode($statusCode)->setJSON($payload);
		}

		return redirect()->back()->withInput()->with('buyer_profile_error', $message);
	}
}
