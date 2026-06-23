<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContingentConfirmations extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'event_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'contingent_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'confirmation_code' => ['type' => 'VARCHAR', 'constraint' => 50],
            'official_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'official_phone' => ['type' => 'VARCHAR', 'constraint' => 50],
            'signature_image' => ['type' => 'LONGTEXT', 'null' => true],
            'signature_hash' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'contingent_snapshot' => ['type' => 'TEXT', 'null' => true],
            'statement' => ['type' => 'TEXT', 'null' => true],
            'confirmed_at' => ['type' => 'DATETIME'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('confirmation_code');
        $this->forge->addUniqueKey(['event_id', 'contingent_id'], 'unique_event_contingent_confirmation');
        $this->forge->addKey('event_id');
        $this->forge->addKey('contingent_id');
        $this->forge->addKey('confirmed_at');
        $this->forge->createTable('contingent_confirmations');
    }

    public function down()
    {
        $this->forge->dropTable('contingent_confirmations', true);
    }
}
