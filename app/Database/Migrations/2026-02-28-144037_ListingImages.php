<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ListingImages extends Migration
{
    public function up()
    {
        //listing_images	image_id                  PK	INT	listing_id                FK   land_listings.listing_id	INT	image_path	VARCHAR	is_primary	BOOLEAN	uploaded_at	DATETIME
        $data = [
            'image_id' => [
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
            'image_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'is_primary' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'uploaded_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ];
        $this->forge->addField($data);
        $this->forge->addKey('image_id', true);
        $this->forge->addForeignKey('listing_id', 'land_listings', 'listing_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('listing_images');
    }

    public function down()
    {
        //
        $this->forge->dropTable('listing_images');
    }
}
