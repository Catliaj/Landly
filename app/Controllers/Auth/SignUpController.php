<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Models\SellerVerificationModel;

class SignUpController extends BaseController
{
    public function buyerSignUp()
    {
        // Logic for buyer sign-up
        $buyerData = $this->request->getPost();

        $buyerData['roles'] = 'buyer';

        $userModel = new UserModel();
        $verificationModel = new SellerVerificationModel();

        // Check if email already exists
        $existingEmail = $userModel->checkEmailExists($buyerData['email']);
        if ($existingEmail) {
            return $this->response->setJSON(['message' => 'Email already exists'])->setStatusCode(ResponseInterface::HTTP_CONFLICT);
        }

        // Create profile pictures folder if it doesn't exist
        if (!is_dir(WRITEPATH . 'uploads/profile_pictures')) {
            mkdir(WRITEPATH . 'uploads/profile_pictures', 0755, true);
        }

        // Store profile picture
        $profileImage = $this->request->getFile('profile_image');
        if ($profileImage && $profileImage->isValid() && !$profileImage->hasMoved()) {
            $buyerData['profile_picture'] = $profileImage->store('profile_pictures');
        }

        $buyerId = $userModel->createUser($buyerData);

        // Store valid ID document
        if (!is_dir(WRITEPATH . 'uploads/buyer_documents')) {
            mkdir(WRITEPATH . 'uploads/buyer_documents', 0755, true);
        }

        $validId = $this->request->getFile('valid_id');
        if ($validId && $validId->isValid() && !$validId->hasMoved()) {
            $storedPath = $validId->store('buyer_documents');
            $verificationModel->addSellerDocument($buyerId, 'Valid ID', $storedPath);
        }

        return $this->response->setJSON(['message' => 'Buyer sign-up successful'])->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function sellerSignUp()
    {
        $sellerData = $this->request->getPost();
        $sellerData['roles'] = 'seller';
        $userModel = new UserModel();
        $verificationModel = new SellerVerificationModel();

        // Check if email exists
        $existingEmail = $userModel->checkEmailExists($sellerData['email']);
        if ($existingEmail && $sellerData['roles'] === 'seller') {
            return $this->response->setJSON([
                'message' => 'Email already exists'
            ])->setStatusCode(ResponseInterface::HTTP_CONFLICT);
        }

        //create a folder destination for profile picture if folder exist  do not create folder else if folder does not exist create folder name profile_pictures
        if (!is_dir(WRITEPATH . 'uploads/profile_pictures')) {
            mkdir(WRITEPATH . 'uploads/profile_pictures', 0755, true);
        }

        //store profile picture in the folder destination and get the stored path
        $file = $this->request->getFile('profile_image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $sellerData['profile_picture'] = $file->store('profile_pictures');
        }

        // Create seller user
        $sellerId = $userModel->createUser($sellerData);

        // Create seller documents folder if it doesn't exist
        if (!is_dir(WRITEPATH . 'uploads/seller_documents')) {
            mkdir(WRITEPATH . 'uploads/seller_documents', 0755, true);
        }

        // Store valid ID (required)
        $validId = $this->request->getFile('valid_id');
        if ($validId && $validId->isValid() && !$validId->hasMoved()) {
            $storedPath = $validId->store('seller_documents');
            $verificationModel->addSellerDocument($sellerId, 'Valid ID', $storedPath);
        }

        // store selfie with valid ID (required)
        $selfieWithId = $this->request->getFile('selfie_with_id');
        if ($selfieWithId && $selfieWithId->isValid() && !$selfieWithId->hasMoved()) {
            $storedPath = $selfieWithId->store('seller_documents');
            $verificationModel->addSellerDocument($sellerId, 'Selfie with Valid ID', $storedPath);
        }
        

        return $this->response->setJSON([
            'message' => 'Seller sign-up successful'
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
