<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ListingAnalytics extends Migration
{
    public function up()
    {
        //listing_analytics	analytics_id              PK	INT	listing_id                FK   land_listings.listing_id	INT	total_views	INT	total_inquiries	INT	total_reservations	INT	total_closed	INT	last_viewed_at	DATETIME
        $data = [
            'analytics_id' => [
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
            'total_views' => [
                'type'       => 'INT',
                'default'    => 0,
            ],
            'total_inquiries' => [
                'type'       => 'INT',
                'default'    => 0,
            ],
            'total_reservations' => [
                'type'       => 'INT',
                'default'    => 0,
            ],
            'total_closed' => [
                'type'       => 'INT',
                'default'    => 0,
            ],
            'last_viewed_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ];
        $this->forge->addField($data);
        $this->forge->addKey('analytics_id', true);
        $this->forge->addForeignKey('listing_id', 'land_listings', 'listing_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('listing_analytics');
    }

    public function down()
    {
        //
        $this->forge->dropTable('listing_analytics');
    }
}
