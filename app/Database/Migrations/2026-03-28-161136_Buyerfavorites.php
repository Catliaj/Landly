<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Buyerfavorites extends Migration
{
    public function up()
    {
        $data = [
            'favorite_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'buyer_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'listing_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
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
        $this->forge->addKey('favorite_id', true);
        $this->forge->addKey('buyer_id');
        $this->forge->addKey('listing_id');
        $this->forge->addUniqueKey(['buyer_id', 'listing_id']);

        $this->forge->addForeignKey('buyer_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('listing_id', 'land_listings', 'listing_id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('buyer_favorites');
    }

    public function down()
    {
        $this->forge->dropTable('buyer_favorites');
    }
}
