<?php

namespace App\Commands;

use App\Models\AdminModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateAdmin extends BaseCommand
{
    protected $group = 'Complaints';
    protected $name = 'complaints:create-admin';
    protected $description = 'Buat atau update akun admin dengan password unik.';

    public function run(array $params)
    {
        $username = trim((string) (CLI::getOption('username') ?? $params[0] ?? ''));
        $name = trim((string) (CLI::getOption('name') ?? $params[1] ?? 'Admin Panitia'));
        $password = (string) (CLI::getOption('password') ?? $params[2] ?? '');

        if ($username === '') {
            CLI::error('Gunakan --username=USERNAME');
            return EXIT_ERROR;
        }

        if ($password === '') {
            $password = CLI::prompt('Password', null, 'required');
        }

        if (strlen($password) < 6) {
            CLI::error('Password minimal 6 karakter untuk production.');
            return EXIT_ERROR;
        }

        $model = new AdminModel();
        $existing = $model->where('username', $username)->first();
        $payload = [
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'name' => $name !== '' ? $name : $username,
        ];

        if ($existing) {
            $model->update($existing['id'], $payload);
            CLI::write('Admin diperbarui: ' . $username, 'green');
            return EXIT_SUCCESS;
        }

        $model->insert($payload);
        CLI::write('Admin dibuat: ' . $username, 'green');
        return EXIT_SUCCESS;
    }
}
