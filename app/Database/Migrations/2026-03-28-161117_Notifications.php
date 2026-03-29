<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Notifications extends Migration
{
    public function up()
    {
        $data = [
            'notification_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'notification_type' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'message_received',
                    'listing_status_changed',
                    'inquiry_status_changed',
                    'message_read_state_changed',
                ],
            ],
            'notification_status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'archived'],
                'default'    => 'active',
            ],
            'listing_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'inquiry_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'message_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'is_read' => [
                'type'    => 'BOOLEAN',
                'default' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ];

        $this->forge->addField($data);
        $this->forge->addKey('notification_id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey(['user_id', 'is_read', 'created_at']);
        $this->forge->addKey(['notification_type', 'created_at']);

        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('listing_id', 'land_listings', 'listing_id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('inquiry_id', 'inquiries', 'inquiry_id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('message_id', 'messages', 'message_id', 'SET NULL', 'CASCADE');

        $this->forge->createTable('notifications');
    }

    public function down()
    {
        $this->forge->dropTable('notifications');
    }
}
