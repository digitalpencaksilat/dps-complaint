<?php

namespace App\Models;

use CodeIgniter\Model;

class ComplaintStatusHistoryModel extends Model
{
    protected $table = 'complaint_status_histories';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['complaint_report_id','old_status','new_status','note','public_note','changed_by_admin_id','changed_at','created_at'];
}
