<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitialSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        $this->db->table('admins')->ignore(true)->insert([
            'username' => 'admin',
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'name' => 'Admin Panitia',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $this->db->table('events')->ignore(true)->insert([
            'name' => 'Testing Event',
            'slug' => 'testing-event',
            'location' => 'Jakarta',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+2 days')),
            'complaint_deadline' => date('Y-m-d H:i:s', strtotime('+7 days')),
            'sla_hours' => 24,
            'status' => 'active',
            'source_db_host' => '127.0.0.1',
            'source_db_name' => 'db_testing_event',
            'source_db_username' => 'root',
            'source_db_password_encrypted' => '',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
