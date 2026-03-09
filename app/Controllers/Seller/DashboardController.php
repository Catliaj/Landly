<?php

namespace App\Controllers\Seller;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class DashboardController extends BaseController
{
    public function index(): ResponseInterface|string
    {
        $userId = $this->getCurrentUserId();
        $Fullname = $this->getCurrentUserFullName();

        if ($userId <= 0) {
            return $this->response->setStatusCode(401)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ]);
        }

        return view('Pages/Seller/Dashboard_Seller', ['fullname' => $Fullname]);
    }

    private function getCurrentUserFullName(): string
    {
        $userId = $this->getCurrentUserId();
        if ($userId <= 0) {
            return '';
        }

        $userModel = new \App\Models\UserModel();
        return $userModel->getFullnameById($userId) ?? '';
    }
}
