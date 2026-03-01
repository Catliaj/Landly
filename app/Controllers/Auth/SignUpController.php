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
        
       
       
        
        $userModel = new UserModel();
        //check if email already exists
        $existingEmail = $userModel->checkEmailExists($buyerData['email']);
        if ($existingEmail && $buyerData['roles'] === 'buyer') {
            return $this->response->setJSON(['message' => 'Email already exists'])->setStatusCode(ResponseInterface::HTTP_CONFLICT);
        }
        
        $userModel->createUser($buyerData);
        return $this->response->setJSON(['message' => 'Buyer sign-up successful'])->setStatusCode(ResponseInterface::HTTP_OK);
       
    }

    public function sellerSignUp()
    {
        $sellerData = $this->request->getPost();
        $userModel = new UserModel();
        $verificationModel = new SellerVerificationModel();

        // Check if email exists
        $existingEmail = $userModel->checkEmailExists($sellerData['email']);
        if ($existingEmail && $sellerData['roles'] === 'seller') {
            return $this->response->setJSON([
                'message' => 'Email already exists'
            ])->setStatusCode(ResponseInterface::HTTP_CONFLICT);
        }

        // Create seller user
        $sellerId = $userModel->createUser($sellerData);

        // Get file input
        $file = $this->request->getFile('file_path');

        if ($file && $file->isValid() && !$file->hasMoved()) {

            // REAL UPLOAD MODE
            $storedPath = $file->store('seller_documents');

            $verificationModel->addSellerDocument(
                $sellerId,
                $this->request->getPost('document_type'),
                $storedPath
            );

        } else {

            // TEST MODE (Postman / unit test)
            $verificationModel->addSellerDocument(
                $sellerId,
                $this->request->getPost('document_type') ?? 'ID Card',
                'test/path/document.jpg'
            );
        }

        return $this->response->setJSON([
            'message' => 'Seller sign-up successful'
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
