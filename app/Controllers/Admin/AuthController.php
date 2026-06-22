<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminModel;

class AuthController extends BaseController
{
    public function login()
    {
        return view('admin/auth/login');
    }

    public function attempt()
    {
        $admin = (new AdminModel())->where('username', $this->request->getPost('username'))->first();
        if (! $admin || ! password_verify((string)$this->request->getPost('password'), $admin['password_hash'])) {
            return redirect()->back()->with('error', 'Login gagal.');
        }
        session()->set(['admin_id' => $admin['id'], 'admin_name' => $admin['name'], 'is_admin' => true]);
        return redirect()->to('/admin/complaints');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/admin/login');
    }
}
