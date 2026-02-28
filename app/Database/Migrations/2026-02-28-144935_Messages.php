<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Messages extends Migration
{
    public function up()
    {
        //messages	message_id                PK	INT	session_id                FK   message_sessions.session_id	INT	sender_id                 FK   users.user_id	INT	message_text	TEXT	attachment_path           nullable	VARCHAR	is_auto_reply	BOOLEAN	is_read	BOOLEAN	sent_at	DATETIME
        $data = [
            'message_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'session_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'sender_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
            ],
            'message_text' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'attachment_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'is_auto_reply' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'is_read' => [
                'type'       => 'BOOLEAN',
                'default'    => false,
            ],
            'sent_at' => [
                'type'       => 'DATETIME',
                null         => true,
            ],
        ];
        $this->forge->addField($data);
        $this->forge->addKey('message_id', true);
        $this->forge->addForeignKey('session_id', 'message_sessions', 'session_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('sender_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('messages');
    }

    public function down()
    {
        //
        $this->forge->dropTable('messages');
    }
}
