<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Inquries extends Migration
{
    public function up()
    {
        //inquiries	inquiry_id                PK	INT	listing_id                FK   land_listings.listing_id	INT	buyer_id                  FK   users.user_id	INT	seller_id                 FK   users.user_id	INT	inquiry_status            CHECK ENUM(pending, accepted, rejected, reserved, closed)	VARCHAR	created_at	DATETIME	updated_at	DATETIME
        $data = [
            'inquiry_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'listing_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'buyer_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'seller_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'inquiry_status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'accepted', 'rejected', 'reserved', 'closed'],
                'default'    => 'pending',
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ];
        $this->forge->addField($data);
        $this->forge->addKey('inquiry_id', true);
        $this->forge->addForeignKey('listing_id', 'land_listings', 'listing_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('buyer_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('seller_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('inquiries');
    }

    public function down()
    {
        //
        $this->forge->dropTable('inquiries');
    }
}
