<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SellerVerificationDocuments extends Migration
{
    public function up()
    {
        //seller_verification_documents	document_id               PK	INT	seller_id                 FK   users.user_id	INT	document_type             CHECK ENUM(valid_id, title_copy, tax_declaration, proof_of_ownership, other)	VARCHAR	file_path	VARCHAR	is_verified	BOOLEAN	reviewed_by               FK   users.user_id, admin	INT	reviewed_at	DATETIME	uploaded_at	DATETIME
        $data = [
            'document_id' => [
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
            'document_type' => [
                'type'       => 'ENUM',
                'constraint' => ['valid_id', 'title_copy', 'tax_declaration', 'proof_of_ownership', 'other'],
            ],
            'file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'is_verified' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'reviewed_by' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'reviewed_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'uploaded_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ];
        $this->forge->addField($data);
        $this->forge->addKey('document_id', true);
        $this->forge->addForeignKey('seller_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('reviewed_by', 'users', 'user_id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('seller_verification_documents');
    }

    public function down()
    {
        //
        $this->forge->dropTable('seller_verification_documents');
    }
}
