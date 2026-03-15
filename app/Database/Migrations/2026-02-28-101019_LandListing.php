<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class LandListing extends Migration
{
    public function up()
    {
        //land_listings	listing_id            PK	INT	seller_id             FK   users.user_id	INT	title	VARCHAR	description	TEXT	barangay	VARCHAR	city	VARCHAR	province	VARCHAR	road_access_type      CHECK ENUM(cemented, right_of_way, none)	VARCHAR	view_type             CHECK ENUM(sea_view, mountain_view, none)	VARCHAR	property_type         CHECK ENUM(residential_land, agricultural_land, commercial_land)	VARCHAR	is_titled	BOOLEAN	has_tax_declaration	BOOLEAN	has_lra_approved_plan	BOOLEAN	mother_title_disclosed	BOOLEAN	document_status       CHECK ENUM(complete, partial, pending)	VARCHAR	investment_ready	BOOLEAN	developing_area	BOOLEAN	listing_status        CHECK ENUM(available, in_inquiry, reserved, closed)	VARCHAR	is_verified_listing	BOOLEAN	created_at	DATETIME	updated_at	DATETIME
        $data = [
            'listing_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'seller_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'barangay' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'city' => [
                'type'       => 'VARCHAR',  
                'constraint' => 255,
            ],
            'province' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'road_access_type' => [
                'type'       => 'ENUM',
                'constraint' => ['cemented', 'right_of_way', 'none'],
            ],
            'view_type' => [
                'type'       => 'ENUM',
                'constraint' => ['sea_view', 'mountain_view', 'none'],
            ],
            'property_type' => [
                'type'       => 'ENUM',
                'constraint' => ['residential_land', 'agricultural_land', 'commercial_land'],
            ],
            'is_titled' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'has_tax_declaration' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'has_lra_approved_plan' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'mother_titled_disclosed' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'document_status' => [
                'type'       => 'ENUM',
                'constraint' => ['complete', 'partial', 'pending'],
            ],
            'investment_ready' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'developing_area' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'listing_status' => [
                'type'       => 'ENUM',
                'constraint' => ['available', 'in_inquiry', 'reserved', 'closed'],
            ],
            'is_verified_listing' => [
                'type'       => 'ENUM',
                'constraint' => ['true', 'false', 'pending', 'rejected'],
                'default'    => 'pending',
            ],
            'price' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'default'    => 0.00,
            ],
            // Other fields to be added here...
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
        $this->forge->addKey('listing_id', true);
        $this->forge->addForeignKey('seller_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('land_listings');
    }

    public function down()
    {
        //
        $this->forge->dropTable('land_listings');
    }
}
