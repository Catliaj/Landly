<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Models\SellerVerificationModel;

class AuthController extends BaseController
{
    public function login(): ResponseInterface
    {
        $userModel = new UserModel();
        $sellerModel = new SellerVerificationModel();

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // 1️⃣ Check if user exists
        $user = $userModel->where('email', $email)->first();

        if (!$user) {
            return $this->response->setJSON([
                'message' => 'Invalid email or password'
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // 2️⃣ Verify password
        if (!password_verify($password, $user['password'])) {
            return $this->response->setJSON([
                'message' => 'Invalid email or password'
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // 3️⃣ Seller verification check
        $sellerVerified = null;

        if (strpos($user['roles'], 'seller') !== false) {

            $verification = $sellerModel
                ->where('seller_id', $user['user_id'])
                ->first();

            $sellerVerified = $verification
                ? (bool) $verification['is_verified']
                : false;

            // 🚫 Block unverified seller login (recommended)
            if (!$sellerVerified) {
                return $this->response->setJSON([
                    'message' => 'Seller account not verified yet'
                ])->setStatusCode(ResponseInterface::HTTP_FORBIDDEN);
            }
        }

        // 4️⃣ Success response
        return $this->response->setJSON([
            'message' => 'Login successful',
            'user' => [
                'user_id' => $user['user_id'],
                'email' => $user['email'],
                'roles' => $user['roles'],
                'seller_verified' => $sellerVerified
            ]
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }
}