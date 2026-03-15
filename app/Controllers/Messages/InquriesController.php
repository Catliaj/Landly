<?php

namespace App\Controllers\Messages;

use App\Controllers\BaseController;
use App\Models\InquriesModel;
use App\Models\LandListings;
use CodeIgniter\HTTP\ResponseInterface;

class InquriesController extends BaseController
{
    private const ALLOWED_STATUSES = ['pending', 'accepted', 'rejected', 'reserved', 'closed'];

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
            ->select('i.inquiry_id, i.listing_id, i.buyer_id, i.seller_id, i.inquiry_status, i.created_at, i.updated_at, l.title AS listing_title, l.price AS listing_price')
            ->join('land_listings l', 'l.listing_id = i.listing_id', 'left')
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

        if ($userId === $buyerId && in_array($status, ['accepted', 'rejected', 'reserved'], true)) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Buyer cannot set this status.',
            ]);
        }

        $inquiryModel->update($inquiryId, [
            'inquiry_status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Inquiry status updated.',
            'inquiry_id' => $inquiryId,
            'inquiry_status' => $status,
        ]);
    }
}
