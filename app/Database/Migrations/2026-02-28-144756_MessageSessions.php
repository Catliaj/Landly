<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MessageSessions extends Migration
{
    public function up()
    {
        //message_sessions	session_id                PK	INT	listing_id                FK   land_listings.listing_id	INT	inquiry_id                FK   inquiries.inquiry_id	INT	buyer_id                  FK   users.user_id	INT	seller_id                 FK   users.user_id	INT	session_status            CHECK ENUM(active, reserved, closed, cancelled)	VARCHAR	last_message_at	DATETIME	started_at	DATETIME	(listing_id, buyer_id)	UNIQUE
        $data = [
            'session_id' => [
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
            'inquiry_id' => [
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
            'session_status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'reserved', 'closed', 'cancelled'],
                'default'    => 'active',
            ],
            'last_message_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'started_at' => [
                'type'       => 'DATETIME',
                null         => true,
            ],
        ];
        $this->forge->addField($data);
        $this->forge->addKey('session_id', true);
        $this->forge->addForeignKey('listing_id', 'land_listings', 'listing_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('inquiry_id', 'inquiries', 'inquiry_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('buyer_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('seller_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['listing_id', 'buyer_id']);
        $this->forge->createTable('message_sessions');
    }

    public function down()
    {
        //
        $this->forge->dropTable('message_sessions');
    }
}
