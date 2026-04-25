<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ListingDailyViews extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'daily_view_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'listing_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'viewer_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'view_date' => [
                'type' => 'DATE',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('daily_view_id', true);
        $this->forge->addKey('listing_id');
        $this->forge->addKey('viewer_user_id');
        $this->forge->addUniqueKey(['listing_id', 'viewer_user_id', 'view_date']);
        $this->forge->addForeignKey('listing_id', 'land_listings', 'listing_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('viewer_user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('listing_daily_views');
    }

    public function down()
    {
        $this->forge->dropTable('listing_daily_views');
    }
}
