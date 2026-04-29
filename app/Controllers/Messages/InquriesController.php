<?php

namespace App\Controllers\Messages;

use App\Controllers\BaseController;
use App\Models\InquriesModel;
use App\Models\LandListings;
use App\Models\NotificationModel;
use CodeIgniter\HTTP\ResponseInterface;

class InquriesController extends BaseController
{
    private const ALLOWED_STATUSES = ['pending', 'accepted', 'rejected', 'reserved', 'closed', 'sold'];

    public function index(): ResponseInterface
    {
        return $this->listInquiries();
    }

    public function listInquiries(): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ]);
        }

        $inquiries = db_connect()
            ->table('inquiries i')
            ->select('i.inquiry_id, i.listing_id, i.buyer_id, i.seller_id, i.inquiry_status, i.created_at, i.updated_at, l.title AS listing_title, l.price AS listing_price, ms.session_id, bu.first_name AS buyer_first_name, bu.last_name AS buyer_last_name, su.first_name AS seller_first_name, su.last_name AS seller_last_name')
            ->join('land_listings l', 'l.listing_id = i.listing_id', 'left')
            ->join('message_sessions ms', 'ms.inquiry_id = i.inquiry_id', 'left')
            ->join('users bu', 'bu.user_id = i.buyer_id', 'left')
            ->join('users su', 'su.user_id = i.seller_id', 'left')
            ->groupStart()
            ->where('i.buyer_id', $userId)
            ->orWhere('i.seller_id', $userId)
            ->groupEnd()
            ->orderBy('i.created_at', 'DESC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'inquiries' => $inquiries,
        ]);
    }

    public function viewInquiry(int $inquiryId): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ]);
        }

        $inquiry = db_connect()
            ->table('inquiries i')
            ->select('i.inquiry_id, i.listing_id, i.buyer_id, i.seller_id, i.inquiry_status, i.created_at, i.updated_at, l.title AS listing_title, l.price AS listing_price')
            ->join('land_listings l', 'l.listing_id = i.listing_id', 'left')
            ->where('i.inquiry_id', $inquiryId)
            ->groupStart()
            ->where('i.buyer_id', $userId)
            ->orWhere('i.seller_id', $userId)
            ->groupEnd()
            ->get()
            ->getRowArray();

        if ($inquiry === null) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Inquiry not found or access denied.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'inquiry' => $inquiry,
        ]);
    }

    public function createInquiry(): ResponseInterface
    {
        $buyerId = $this->getCurrentUserId();
        if ($buyerId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ]);
        }

        $input = $this->request->getJSON(true) ?? [];
        $listingId = (int) (
            $input['listing_id']
            ?? $input['listingId']
            ?? $input['PropertyID']
            ?? $input['propertyId']
            ?? $input['property_id']
            ?? $input['listing']
            ?? $this->request->getPost('listing_id')
            ?? $this->request->getPost('listingId')
            ?? $this->request->getPost('PropertyID')
            ?? $this->request->getPost('propertyId')
            ?? $this->request->getPost('property_id')
            ?? $this->request->getPost('listing')
            ?? $this->request->getGetPost('listing_id')
            ?? $this->request->getGetPost('listingId')
            ?? $this->request->getGetPost('PropertyID')
            ?? $this->request->getGetPost('propertyId')
            ?? $this->request->getGetPost('property_id')
            ?? $this->request->getGetPost('listing')
            ?? 0
        );

        if ($listingId <= 0) {
            $fallbackListing = db_connect()
                ->table('land_listings')
                ->select('listing_id')
                ->where('is_verified_listing', 'true')
                ->where('seller_id !=', $buyerId)
                ->orderBy('listing_id', 'DESC')
                ->get()
                ->getRowArray();

            $listingId = (int) ($fallbackListing['listing_id'] ?? 0);
        }

        if ($listingId <= 0) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'listing_id is required. Accepted keys: listing_id, listingId, PropertyID, propertyId, property_id, listing.',
            ]);
        }

        $listingModel = new LandListings();
        $inquiryModel = new InquriesModel();

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
                'message' => 'Cannot create inquiry for your own listing.',
            ]);
        }

        $existingInquiry = $inquiryModel
            ->where('listing_id', $listingId)
            ->where('buyer_id', $buyerId)
            ->where('seller_id', $sellerId)
            ->whereIn('inquiry_status', ['pending', 'accepted', 'reserved'])
            ->orderBy('inquiry_id', 'DESC')
            ->first();

        if ($existingInquiry !== null) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Inquiry already exists for this listing.',
                'inquiry_id' => (int) ($existingInquiry['inquiry_id'] ?? 0),
                'inquiry_status' => (string) ($existingInquiry['inquiry_status'] ?? 'pending'),
            ]);
        }

        $now = date('Y-m-d H:i:s');
        $inquiryId = (int) $inquiryModel->insert([
            'listing_id' => $listingId,
            'buyer_id' => $buyerId,
            'seller_id' => $sellerId,
            'inquiry_status' => 'pending',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->createNotificationSafely([
            'user_id' => $sellerId,
            'notification_type' => 'inquiry_status_changed',
            'notification_status' => 'active',
            'listing_id' => $listingId,
            'inquiry_id' => $inquiryId,
            'message_id' => null,
            'message' => 'You received a new inquiry for your listing.',
            'is_read' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return $this->response->setStatusCode(201)->setJSON([
            'status' => 'success',
            'message' => 'Inquiry created successfully.',
            'inquiry_id' => $inquiryId,
            'listing_id' => $listingId,
            'seller_id' => $sellerId,
            'inquiry_status' => 'pending',
        ]);
    }

    public function updateInquiryStatus(int $inquiryId): ResponseInterface
    {
        $userId = $this->getCurrentUserId();
        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ]);
        }

        $input = $this->request->getJSON(true) ?? [];
        $status = strtolower(trim((string) ($input['inquiry_status'] ?? $this->request->getPost('inquiry_status') ?? '')));

        if (! in_array($status, self::ALLOWED_STATUSES, true)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'error',
                'message' => 'Invalid inquiry_status. Allowed: pending, accepted, rejected, reserved, closed.',
            ]);
        }

        $inquiryModel = new InquriesModel();
        $inquiry = $inquiryModel->find($inquiryId);

        if ($inquiry === null) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Inquiry not found.',
            ]);
        }

        $sellerId = (int) ($inquiry['seller_id'] ?? 0);
        $buyerId = (int) ($inquiry['buyer_id'] ?? 0);

        if ($userId !== $sellerId && $userId !== $buyerId) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Forbidden: You cannot update this inquiry.',
            ]);
        }

        if ($userId === $buyerId && in_array($status, ['accepted', 'rejected', 'reserved', 'sold'], true)) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Buyer cannot set this status.',
            ]);
        }

        $inquiryModel->update($inquiryId, [
            'inquiry_status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $now = date('Y-m-d H:i:s');
        $recipientId = $userId === $sellerId ? $buyerId : $sellerId;
        if ($recipientId > 0) {
            $this->createNotificationSafely([
                'user_id' => $recipientId,
                'notification_type' => 'inquiry_status_changed',
                'notification_status' => 'active',
                'listing_id' => (int) ($inquiry['listing_id'] ?? 0) ?: null,
                'inquiry_id' => $inquiryId,
                'message_id' => null,
                'message' => 'Inquiry status updated to ' . $status . '.',
                'is_read' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $listingStatus = $this->mapInquiryStatusToListingStatus($status);
        if ($listingStatus !== null) {
            $listingModel = new LandListings();
            $listingId = (int) ($inquiry['listing_id'] ?? 0);
            if ($listingId > 0) {
                $listing = $listingModel->find($listingId);
                $previousListingStatus = (string) ($listing['listing_status'] ?? '');

                if ($previousListingStatus !== $listingStatus) {
                    $listingModel->update($listingId, [
                        'listing_status' => $listingStatus,
                        'updated_at' => $now,
                    ]);

                    $forUsers = array_values(array_filter(array_unique([
                        (int) ($inquiry['seller_id'] ?? 0),
                        (int) ($inquiry['buyer_id'] ?? 0),
                    ])));

                    foreach ($forUsers as $targetUserId) {
                        $this->createNotificationSafely([
                            'user_id' => $targetUserId,
                            'notification_type' => 'listing_status_changed',
                            'notification_status' => 'active',
                            'listing_id' => $listingId,
                            'inquiry_id' => $inquiryId,
                            'message_id' => null,
                            'message' => 'Listing status updated to ' . $listingStatus . '.',
                            'is_read' => 0,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Inquiry status updated.',
            'inquiry_id' => $inquiryId,
            'inquiry_status' => $status,
        ]);
    }

    private function mapInquiryStatusToListingStatus(string $inquiryStatus): ?string
    {
        return match ($inquiryStatus) {
            'accepted' => 'in_inquiry',
            'reserved' => 'reserved',
            'closed' => 'closed',
            'sold' => 'sold',
            default => null,
        };
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
