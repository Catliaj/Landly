<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ListingDocuments extends Migration
{
    public function up()
    {
        //listing_documents	document_id               PK	INT	listing_id                FK   land_listings.listing_id	INT	document_type             CHECK ENUM(title, tax_declaration, lra_plan, other)	VARCHAR	file_path	VARCHAR	is_verified	BOOLEAN	uploaded_at	DATETIME
        $data = [
            'document_id' => [
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
            'document_type' => [
                'type'       => 'ENUM',
                'constraint' => ['title', 'tax_declaration', 'lra_plan', 'other'],
            ],
            'file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'is_verified' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'uploaded_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ];
        $this->forge->addField($data);
        $this->forge->addKey('document_id', true);
        $this->forge->addForeignKey('listing_id', 'land_listings', 'listing_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('listing_documents');
    }

    public function down()
    {
        //
        $this->forge->dropTable('listing_documents');
    }
}
