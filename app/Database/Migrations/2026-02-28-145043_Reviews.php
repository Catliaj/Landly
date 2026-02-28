<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Reviews extends Migration
{
    public function up()
    {
        //reviews	review_id                 PK	INT	reviewer_id               FK   users.user_id	INT	seller_id                 FK   users.user_id	INT	listing_id                FK   land_listings.listing_id	INT	rating                    CHECK 1-5	TINYINT	comment	TEXT	created_at	DATETIME
        $data = [
            'review_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'reviewer_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'seller_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'listing_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'rating' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'comment' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                null         => true,
            ],
        ];
        $this->forge->addField($data);
        $this->forge->addKey('review_id', true);
        $this->forge->addForeignKey('reviewer_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('seller_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('listing_id', 'land_listings', 'listing_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('reviews');
    }

    public function down()
    {
        //
        $this->forge->dropTable('reviews');
    }
}
