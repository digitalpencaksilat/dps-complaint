<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateComplaintSchema extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 100],
            'location' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'start_date' => ['type' => 'DATE', 'null' => true],
            'end_date' => ['type' => 'DATE', 'null' => true],
            'complaint_deadline' => ['type' => 'DATETIME', 'null' => true],
            'complaint_closed_at' => ['type' => 'DATETIME', 'null' => true],
            'complaint_closed_reason' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'sla_hours' => ['type' => 'INT', 'default' => 24],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'draft'],
            'source_db_host' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'source_db_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'source_db_username' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'source_db_password_encrypted' => ['type' => 'TEXT', 'null' => true],
            'source_config' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->addKey('status');
        $this->forge->addKey('complaint_deadline');
        $this->forge->createTable('events');

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'event_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'source_contingent_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'source_event_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('event_id');
        $this->forge->addKey('name');
        $this->forge->addUniqueKey(['event_id', 'source_contingent_id'], 'unique_event_source_contingent');
        $this->forge->createTable('contingents');

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'event_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'source_participant_id' => ['type' => 'VARCHAR', 'constraint' => 100],
            'source_competition_type' => ['type' => 'VARCHAR', 'constraint' => 30],
            'source_registrant_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'full_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'contingent_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'contingent_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'gender' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'age_category' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'competition_category' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'class_or_art_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'source_event_id' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'raw_payload' => ['type' => 'TEXT', 'null' => true],
            'imported_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('event_id');
        $this->forge->addKey('source_participant_id');
        $this->forge->addKey('full_name');
        $this->forge->addKey('contingent_name');
        $this->forge->addUniqueKey(['event_id', 'source_participant_id'], 'unique_event_source_participant');
        $this->forge->createTable('participants');

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'event_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'ticket_code' => ['type' => 'VARCHAR', 'constraint' => 50],
            'official_name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'official_phone' => ['type' => 'VARCHAR', 'constraint' => 50],
            'signature_image' => ['type' => 'LONGTEXT', 'null' => true],
            'signature_hash' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'signed_at' => ['type' => 'DATETIME', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'baru'],
            'admin_note' => ['type' => 'TEXT', 'null' => true],
            'submitted_at' => ['type' => 'DATETIME'],
            'sla_due_at' => ['type' => 'DATETIME', 'null' => true],
            'first_processed_at' => ['type' => 'DATETIME', 'null' => true],
            'resolved_at' => ['type' => 'DATETIME', 'null' => true],
            'processed_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('ticket_code');
        $this->forge->addKey('event_id');
        $this->forge->addKey('status');
        $this->forge->addKey('submitted_at');
        $this->forge->createTable('complaint_reports');

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'complaint_report_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'complaint_type' => ['type' => 'VARCHAR', 'constraint' => 50],
            'participant_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'contingent_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'participant_snapshot' => ['type' => 'TEXT', 'null' => true],
            'contingent_snapshot' => ['type' => 'TEXT', 'null' => true],
            'description' => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('complaint_report_id');
        $this->forge->addKey('complaint_type');
        $this->forge->addKey('participant_id');
        $this->forge->addKey('contingent_id');
        $this->forge->createTable('complaint_items');

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'complaint_report_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'old_status' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'new_status' => ['type' => 'VARCHAR', 'constraint' => 30],
            'note' => ['type' => 'TEXT', 'null' => true],
            'public_note' => ['type' => 'TEXT', 'null' => true],
            'changed_by_admin_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'changed_at' => ['type' => 'DATETIME'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('complaint_report_id');
        $this->forge->addKey('new_status');
        $this->forge->addKey('changed_at');
        $this->forge->createTable('complaint_status_histories');

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'username' => ['type' => 'VARCHAR', 'constraint' => 100],
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('username');
        $this->forge->createTable('admins');
    }

    public function down()
    {
        foreach (['admins', 'complaint_status_histories', 'complaint_items', 'complaint_reports', 'participants', 'contingents', 'events'] as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}
