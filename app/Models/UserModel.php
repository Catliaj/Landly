<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'user_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'first_name',
        'last_name',
        'email',
        'password',
        'profile_picture',
        'is_active',
        'roles',
        'created_at',
        'updated_at',
        'last_login',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function createUser($data)
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $data['is_active'] = 1;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    public function getUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    public function verifyPassword($email, $password)
    {
        $user = $this->getUserByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }

    public function updateLastLogin($userId)
    {
        return $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
    }

    public function activateUser($userId)
    {
        return $this->update($userId, ['is_active' => 1]);
    }

    public function deactivateUser($userId)
    {
        return $this->update($userId, ['is_active' => 0]);
    }

    public function getUserById($userId)
    {
        return $this->find($userId);
    }

    public function getAllUsers()
    {
        return $this->findAll();
    }

    public function deleteUser($userId)
    {
        return $this->delete($userId);
    }

    public function checkEmailExists($email)
    {
        //true if email exists, false if not
        return $this->where('email', $email)->countAllResults() > 0;
    }
}
