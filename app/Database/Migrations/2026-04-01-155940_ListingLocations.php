<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ListingLocations extends Migration
{
    public function up()
    {
        //
        $data = [
            'listing_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'latitude' => [
                'type'       => 'DECIMAL',
                'constraint' => [10, 8],
            ],
            'longitude' => [
                'type'       => 'DECIMAL',
                'constraint' => [11, 8],
            ],
        ];

        $this->forge->addField($data);
        $this->forge->addKey('listing_id', true);
        $this->forge->addForeignKey('listing_id', 'land_listings', 'listing_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('listing_locations');
    }

    public function down()
    {
        //
        $this->forge->dropTable('listing_locations');
    }
}
