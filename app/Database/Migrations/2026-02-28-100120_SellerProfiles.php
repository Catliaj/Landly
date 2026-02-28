<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SellerProfiles extends Migration
{
    public function up()
    {
        //seller_profiles	seller_id                 PK, FK   users.user_id	INT	bio	TEXT	achievements	TEXT	total_listings	INT	total_closed_sales	INT	DECIMAL(3		is_verified_seller	BOOLEAN	verification_status       CHECK ENUM(pending, verified, rejected)	VARCHAR	verified_at               nullable	DATETIME	verified_by               FK   users.user_id, admin, nullable	INT	created_at	DATETIME	updated_at	DATETIME

        $data = [
            'seller_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                
            ],
            'bio' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'achievements' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'total_listings' => [
                'type'       => 'INT',
                'default'    => 0,
            ],
            'total_closed_sales' => [
                'type'       => 'INT',
                'default'    => 0,
            ],
            'rating' => [
                'type'       => 'DECIMAL',
                'constraint' => [3, 2],
                'default'    => 0.00,
            ],
            'is_verified_seller' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'verification_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'verified', 'rejected'],
                'default'    => 'pending',
            ],
            'verified_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'verified_by' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            // Foreign key to users table
        ];
        $this->forge->addField($data);
        $this->forge->addForeignKey('seller_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('seller_profiles');
    }

    public function down()
    {
        //
        $this->forge->dropTable('seller_profiles');
    }
}
